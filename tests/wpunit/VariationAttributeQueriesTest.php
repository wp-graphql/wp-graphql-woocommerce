<?php

class VariationAttributeQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedAttributes( $id ) {
		$product    = wc_get_product( $id );
		$attributes = 'variable' === $product->get_type()
			? $product->get_default_attributes()
			: $product->get_attributes();

		$expected = [];
		foreach ( $attributes as $name => $value ) {
			$term        = \get_term_by( 'slug', $value, $name );
			$expected_id = base64_encode( $id . '||' . $name . '||' . $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			if ( ! $term instanceof \WP_Term ) {
				$expected[] = $this->expectedNode(
					'nodes',
					[
						$this->expectedField( 'id', $expected_id ),
						$this->expectedField( 'attributeId', 0 ),
						$this->expectedField( 'name', $name ),
						$this->expectedField( 'value', $value ),
					]
				);
			} else {
				$expected[] = $this->expectedNode(
					'nodes',
					[
						$this->expectedField( 'id', $expected_id ),
						$this->expectedField( 'attributeId', $term->term_id ),
						$this->expectedField( 'name', $term->taxonomy ),
						$this->expectedField( 'value', $term->name ),
					]
				);
			}
		}

		return $expected;
	}
	public function expectedDefaultAttributes( $id ) {
		$product    = wc_get_product( $id );
		$attributes = $product->get_attributes();

		$expected = [];
		foreach ( $attributes as $attribute ) {
			$name = $attribute->get_name();
			if ( $attribute->is_taxonomy() ) {
				$attribute_values = wc_get_product_terms( $id, $attribute->get_name(), [ 'fields' => 'all' ] );
				foreach ( $attribute_values as $attribute_value ) {
					$expected[] = $this->expectedNode(
						'nodes',
						[
							$this->expectedField( 'id', base64_encode( $id . '|' . $name . '|' . $attribute_value->name ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
							$this->expectedField( 'attributeId', $attribute_value->term_id ),
							$this->expectedField( 'name', $name ),
							$this->expectedField( 'value', $attribute_value->name ),
						]
					);
				}
			} else {
				$values = $attribute->get_options();
				foreach ( $values as $attribute_value ) {
					$expected[] = $this->expectedNode(
						'nodes',
						[
							$this->expectedField( 'id', base64_encode( $id . '|' . $name . '|' . $attribute_value ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
							$this->expectedField( 'attributeId', 0 ),
							$this->expectedField( 'name', $name ),
							$this->expectedField( 'value', $attribute_value ),
						]
					);
				}
			}
		}

		return $expected;
	}
	// tests
	public function testProductVariationToVariationAttributeQuery() {
		// Create a product and variation.
		$product_ids  = $this->factory->product_variation->createSome();
		$variation_id = $product_ids['variations'][0];

		// Create a query.
		$query = '
            query fromVariationQuery( $id: ID! ) {
                productVariation( id: $id ) {
                    id
                    variationAttributes {
                        nodes {
                            id
                            attributeId
                            name
                            value
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Test query and results
		 */
		$variables = [ 'id' => $this->toRelayId( 'product_variation', $variation_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'productVariation.id', $this->toRelayId( 'product_variation', $variation_id ) ),
			$this->expectedObject( 'productVariation.variationAttributes', $this->expectedAttributes( $variation_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testVariableProductToVariationAttributeQuery() {
		// Create a variable product w/ default attributes..
		$product_ids = $this->factory->product_variation->createSome();
		$product_id  = $product_ids['product'];

		// Create a query.
		$query = '
            query ( $id: ID! ) {
                product( id: $id ) {
                    ... on VariableProduct {
                        id
                        defaultAttributes {
                            nodes {
                                id
                                attributeId
                                name
                                value
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Test query and results
		 */
		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedObject( 'product.defaultAttributes', $this->expectedAttributes( $product_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testSimpleProductToVariationAttributeQuery() {
		// Create a product w/ default attributes.
		$attribute_data = [
			$this->factory->product->createAttribute( 'size', [ 'small' ] ),
			$this->factory->product->createAttribute( 'color', [ 'red' ] ),
			[
				'attribute_id'       => 0,
				'attribute_taxonomy' => 'logo',
				'term_ids'           => [ 'Yes' ],
			],
		];
		$attributes     = array_map(
			function( $data, $index ) {
				\codecept_debug( $data );
				$attribute = new \WC_Product_Attribute();
				$attribute->set_id( $data['attribute_id'] );
				$attribute->set_name( $data['attribute_taxonomy'] );
				$attribute->set_options( $data['term_ids'] );
				$attribute->set_position( $index );
				$attribute->set_visible( true );
				$attribute->set_variation( false );
				return $attribute;
			},
			$attribute_data,
			array_keys( $attribute_data )
		);
		$product_id     = $this->factory->product->createSimple(
			[
				'attributes'         => $attributes,
				'default_attributes' => [
					'pa_size'  => 'small',
					'pa_color' => 'red',
					'logo'     => 'Yes',
				],
			]
		);

		// Create a query.
		$query = '
            query ( $id: ID! ) {
                product( id: $id ) {
                    ... on SimpleProduct {
                        id
                        defaultAttributes {
                            nodes {
                                id
                                attributeId
                                name
                                value
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Test query and results
		 */
		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedObject( 'product.defaultAttributes', $this->expectedDefaultAttributes( $product_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

}
