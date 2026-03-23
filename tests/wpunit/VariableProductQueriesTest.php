<?php

class VariableProductQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test that defaultAttributes on a VariableProduct returns the correct values.
	 */
	public function testVariableProductDefaultAttributes() {
		$color_attr = $this->factory->product->createAttribute( 'color', [ 'red', 'blue', 'green' ] );
		$size_attr  = $this->factory->product->createAttribute( 'size', [ 'small', 'medium', 'large' ] );

		$product_id = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $color_attr, $size_attr ],
			]
		);

		// Create variations.
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [ 'pa_color' => 'red', 'pa_size' => 'small' ],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [ 'pa_color' => 'blue', 'pa_size' => 'medium' ],
				'image_id'      => null,
				'regular_price' => 15,
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [ 'pa_color' => 'green', 'pa_size' => 'large' ],
				'image_id'      => null,
				'regular_price' => 20,
			]
		);

		// Set default attributes on the variable product.
		$product = wc_get_product( $product_id );
		$product->set_default_attributes(
			[
				'pa_color' => 'blue',
				'pa_size'  => 'medium',
			]
		);
		$product->save();

		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					... on VariableProduct {
						databaseId
						defaultAttributes {
							nodes {
								name
								value
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $product_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.databaseId', $product_id ),
			$this->expectedNode(
				'product.defaultAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_color' ),
					$this->expectedField( 'value', 'blue' ),
				]
			),
			$this->expectedNode(
				'product.defaultAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_size' ),
					$this->expectedField( 'value', 'medium' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test that defaultAttributes returns empty when no defaults are set.
	 */
	public function testVariableProductWithNoDefaultAttributes() {
		$color_attr = $this->factory->product->createAttribute( 'color', [ 'red', 'blue' ] );

		$product_id = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $color_attr ],
			]
		);

		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [ 'pa_color' => 'red' ],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);

		// Explicitly clear default attributes.
		$product = wc_get_product( $product_id );
		$product->set_default_attributes( [] );
		$product->save();

		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					... on VariableProduct {
						databaseId
						defaultAttributes {
							nodes {
								name
								value
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $product_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'product.databaseId', $product_id ) ]
		);

		$nodes = $this->lodashGet( $response, 'data.product.defaultAttributes.nodes', [] );
		$this->assertEmpty( $nodes, 'defaultAttributes should be empty when no defaults are set.' );
	}

	/**
	 * Test querying defaultAttributes alongside variations to identify the default variation.
	 */
	public function testDefaultAttributesMatchVariation() {
		$color_attr = $this->factory->product->createAttribute( 'color', [ 'red', 'blue' ] );
		$size_attr  = $this->factory->product->createAttribute( 'size', [ 'small', 'large' ] );

		$product_id = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $color_attr, $size_attr ],
			]
		);

		$red_small = $this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [ 'pa_color' => 'red', 'pa_size' => 'small' ],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);
		$blue_large = $this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [ 'pa_color' => 'blue', 'pa_size' => 'large' ],
				'image_id'      => null,
				'regular_price' => 20,
			]
		);

		// Set default to blue + large.
		$product = wc_get_product( $product_id );
		$product->set_default_attributes(
			[
				'pa_color' => 'blue',
				'pa_size'  => 'large',
			]
		);
		$product->save();

		// Query both defaultAttributes and variations so the client can match them.
		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					... on VariableProduct {
						defaultAttributes {
							nodes {
								name
								value
							}
						}
						variations {
							nodes {
								databaseId
								attributes {
									nodes {
										name
										value
									}
								}
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $product_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		// Verify defaultAttributes are blue + large.
		$expected = [
			$this->expectedNode(
				'product.defaultAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_color' ),
					$this->expectedField( 'value', 'blue' ),
				]
			),
			$this->expectedNode(
				'product.defaultAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_size' ),
					$this->expectedField( 'value', 'large' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Verify both variations are returned with their attributes.
		$variations = $this->lodashGet( $response, 'data.product.variations.nodes', [] );
		$this->assertCount( 2, $variations );

		// Client-side matching: find the variation whose attributes match the defaults.
		$default_attrs = $this->lodashGet( $response, 'data.product.defaultAttributes.nodes', [] );
		$default_map   = [];
		foreach ( $default_attrs as $attr ) {
			$default_map[ $attr['name'] ] = $attr['value'];
		}

		$default_variation_id = null;
		foreach ( $variations as $variation ) {
			$variation_attrs = [];
			foreach ( $variation['attributes']['nodes'] as $attr ) {
				$variation_attrs[ $attr['name'] ] = $attr['value'];
			}

			if ( $variation_attrs === $default_map ) {
				$default_variation_id = $variation['databaseId'];
				break;
			}
		}

		$this->assertEquals( $blue_large, $default_variation_id, 'The default variation should be blue+large.' );
	}
}
