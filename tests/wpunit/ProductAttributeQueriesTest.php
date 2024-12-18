<?php

class ProductAttributeQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function expectedProductAttributeData( $product_id, $path ) {
		$product    = wc_get_product( $product_id );
		$attributes = $product->get_attributes();

		$expected = [];

		foreach ( $attributes as $attribute_name => $attribute ) {
			$expected[] = $this->expectedNode(
				$path,
				[
					$this->expectedField( 'id', base64_encode( $attribute_name . ':' . $product_id . ':' . $attribute->get_name() ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					$this->expectedField( 'attributeId', $attribute->get_id() ),
					$this->expectedField( 'name', $attribute->get_name() ),
					$this->expectedField(
						'label',
						$attribute->is_taxonomy()
							? get_taxonomy( $attribute->get_name() )->labels->singular_name
							: $attribute->get_name()
					),
					$this->expectedField( 'options', $attribute->get_slugs() ),
					$this->expectedField( 'position', $attribute->get_position() ),
					$this->expectedField( 'visible', $attribute->get_visible() ),
					$this->expectedField( 'variation', $attribute->get_variation() ),
				]
			);
		}

		return $expected;
	}

	// tests
	public function testProductAttributeQuery() {
		$product_id    = $this->factory->product->createVariable();
		$variation_ids = $this->factory->product_variation->createSome( $product_id )['variations'];

		$query = '
            query attributeQuery( $id: ID! ) {
                product( id: $id ) {
                    ... on VariableProduct {
                        id
                        attributes {
                            nodes {
                                id
                                attributeId
								name
								label
                                options
                                position
                                visible
                                variation
                            }
                        }
                    }
                }
            }
        ';

		$variables = [ 'id' => $this->toRelayId( 'post', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_merge(
			[ $this->expectedField( 'product.id', $this->toRelayId( 'post', $product_id ) ) ],
			$this->expectedProductAttributeData( $product_id, 'product.attributes.nodes' )
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductAttributeToProductConnectionQuery() {
		

		// Create noise products.
		$product_id   = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $this->factory->product->createAttribute( 'pattern', [ 'polka-dot', 'stripe', 'flames' ] ) ],
			],
		);
		$variation_id = $this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pattern' => 'polka-dot',
				],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);

		// Create variable product with attribute.
		$other_product_id   = $this->factory->product->createVariable();
		$other_variation_id = $this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pattern' => 'stripe',
				],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);  
		$other_product_id_2 = $this->factory->product->createSimple();
		$this->clearSchema();

		$query = '
            query attributeConnectionQuery( $pattern: [String!] ) {
                allPaPattern( where: { name: $pattern } ) {
                    nodes {
                        products {
                            nodes {
                                id
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assert correct products are queried.
		 */
		$variables = [ 'pattern' => 'polka-dot' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'allPaPattern.nodes.0.products.nodes.0.id', $this->toRelayId( 'post', $product_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductAttributeToVariationConnectionQuery() {
		$product_id    = $this->factory->product->createVariable();
		$variation_ids = $this->factory->product_variation->createSome( $product_id )['variations'];

		$query = '
            query attributeConnectionQuery( $size: [String!] ) {
                allPaSize( where: { name: $size } ) {
                    nodes {
                        variations {
                            nodes {
                                id
                            }
                        }
                    }
                }
            }
        ';

		$variables = [ 'size' => 'small' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_map(
			function ( $id ) {
				return $this->expectedField( 'allPaSize.nodes.0.variations.nodes.#.id', $this->toRelayId( 'post', $id ) );
			},
			array_filter(
				$variation_ids,
				static function ( $id ) {
					$variation       = new \WC_Product_Variation( $id );
					$small_attribute = array_filter(
						$variation->get_attributes(),
						static function ( $attribute ) {
							return 'small' === $attribute;
						}
					);
					return ! empty( $small_attribute );
				}
			)
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductAttributeMatchesVariationAttributeCounterpart() {
		$product_id    = $this->factory->product->createVariable();
		$variation_ids = $this->factory->product_variation->createSome( $product_id )['variations'];

		$query = '
            query attributeQuery( $id: ID! ) {
                product( id: $id ) {
					id
					... on ProductWithAttributes {
						attributes {
							nodes {
								name
								label
								options
							}
						}
					}
					... on ProductWithVariations {
						variations {
							nodes {
								id
								attributes {
									nodes {
										name
										label
										value
									}
								}
							}
						}
					}
                }
            }
        ';

		$variables = [ 'id' => $this->toRelayId( 'post', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		/**
		 * Assert that the product attributes match the variation attributes
		 * without modification to confirm variations can be identified by product attribute.
		 */
		$attributes = $this->lodashGet( $response, 'data.product.attributes.nodes', [] );
		$variations = $this->lodashGet( $response, 'data.product.variations.nodes', [] );

		foreach( $variations as $variation ) {
			$variation_attributes = $this->lodashGet( $variation, 'attributes.nodes', [] );
			foreach( $variation_attributes as $variation_attribute ) {
				$attribute_name = $variation_attribute['name'];
				$attribute = array_search( $attribute_name, array_column( $attributes, 'name' ) );
				$this->assertNotFalse( $attribute, sprintf( 'Variation attribute not found in product attributes for %s', $attribute_name ) );
				if ( "" === $variation_attribute['value'] ) {
					continue;
				}

				$this->assertContains( $variation_attribute['value'], $attributes[ $attribute ]['options'] );
			}
		}
		
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'product.id', $this->toRelayId( 'post', $product_id ) ) ]
		);
	}
}
