<?php

class ProductAttributeQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function expectedProductAttributeData( $product_id, $path ) {
		$product    = wc_get_product( $product_id );
		$attributes = $product->get_attributes();

		$expected = [];

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( $attribute->is_taxonomy() ) {
				$expected_id = \GraphQLRelay\Relay::toGlobalId( 'GlobalProductAttribute', $attribute->get_id() );
			} else {
				$expected_id = \GraphQLRelay\Relay::toGlobalId( 'LocalProductAttribute', $attribute->get_name() . ':' . $product_id );
			}
			$expected[] = $this->expectedNode(
				$path,
				[
					$this->expectedField( 'id', $expected_id ),
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

	public function testProductAttributesQuery() {
		$this->factory->product->createAttribute( 'texture', [ 'smooth', 'rough', 'tiled' ] );
		$this->factory->product->createAttribute( 'tile-size', [ '4x4', '8x8', '12x12' ] );

		$query = '
			query {
				productAttributes {
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
		';

		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedNode(
				'productAttributes.nodes',
				[
					$this->expectedField( 'id', self::NOT_NULL ),
					$this->expectedField( 'name', 'pa_texture' ),
					$this->expectedField( 'label', 'texture' ),
					$this->expectedField( 'options', [ 'rough', 'smooth', 'tiled' ] ),
				]
			),
			$this->expectedNode(
				'productAttributes.nodes',
				[
					$this->expectedField( 'id', self::NOT_NULL ),
					$this->expectedField( 'name', 'pa_tile-size' ),
					$this->expectedField( 'label', 'tile-size' ),
					$this->expectedField( 'options', [ '12x12', '4x4', '8x8' ] ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test that registering a global product attribute with no terms
	 * does not break the GraphQL schema or server.
	 */
	public function testGlobalProductAttributeWithNoTermsDoesNotBreakSchema() {
		// Register a global product attribute with no terms.
		$this->factory->product->createAttribute( 'material', [] );
		$this->clearSchema();

		// Validate the schema is still valid.
		$schema = \WPGraphQL::get_schema();
		$schema->assertValid();

		// Run an introspection query to confirm the type registry is intact.
		$query    = \GraphQL\Type\Introspection::getIntrospectionQuery();
		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		// Query the empty attribute taxonomy directly.
		$query = '
			query {
				allPaMaterial {
					nodes {
						name
						slug
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'allPaMaterial.nodes', static::IS_FALSY ),
			]
		);

		// Confirm productAttributes root query includes the empty attribute.
		$query = '
			query {
				productAttributes {
					nodes {
						name
						label
						options
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'productAttributes.nodes',
					[
						$this->expectedField( 'name', 'pa_material' ),
						$this->expectedField( 'label', 'material' ),
						$this->expectedField( 'options', [] ),
					],

				),
			]
		);
	}
}
