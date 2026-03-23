<?php

class ProductAttributeConnectionsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test the root productAttributes query returns global attributes.
	 */
	public function testProductAttributesRootQuery() {
		$color_attr   = $this->factory->product->createAttribute( 'color', [ 'red', 'blue', 'green' ] );
		$material_attr = $this->factory->product->createAttribute( 'material', [ 'cotton', 'polyester', 'silk' ] );

		$this->clearSchema();

		$query = '
			query {
				productAttributes {
					nodes {
						id
						name
						label
						options
						scope
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedNode(
				'productAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_color' ),
					$this->expectedField( 'label', self::NOT_NULL ),
					$this->expectedField( 'options', self::NOT_NULL ),
					$this->expectedField( 'scope', 'GLOBAL' ),
				]
			),
			$this->expectedNode(
				'productAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_material' ),
					$this->expectedField( 'label', self::NOT_NULL ),
					$this->expectedField( 'options', self::NOT_NULL ),
					$this->expectedField( 'scope', 'GLOBAL' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test the products connection on a GlobalProductAttribute resolved from a product.
	 */
	public function testGlobalProductAttributeToProductsConnection() {
		$weight_attr = $this->factory->product->createAttribute( 'weight', [ 'light', 'heavy' ] );

		$product_a = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $weight_attr ],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_a,
				'attributes'    => [ 'weight' => 'light' ],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);

		$product_b = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $weight_attr ],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_b,
				'attributes'    => [ 'weight' => 'heavy' ],
				'image_id'      => null,
				'regular_price' => 20,
			]
		);

		$unrelated_product = $this->factory->product->createSimple();

		$this->clearSchema();

		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					... on ProductWithAttributes {
						globalAttributes {
							nodes {
								name
								products {
									nodes {
										databaseId
									}
								}
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $product_a ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'product.globalAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_weight' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// The products connection on the attribute should include both products that use this attribute.
		$weight_node = null;
		$global_attrs = $this->lodashGet( $response, 'data.product.globalAttributes.nodes', [] );
		foreach ( $global_attrs as $node ) {
			if ( 'pa_weight' === $node['name'] ) {
				$weight_node = $node;
				break;
			}
		}

		$this->assertNotNull( $weight_node, 'pa_weight attribute should exist.' );
		$product_ids = array_column( $weight_node['products']['nodes'], 'databaseId' );
		$this->assertContains( $product_a, $product_ids );
		$this->assertContains( $product_b, $product_ids );
		$this->assertNotContains( $unrelated_product, $product_ids );
	}

	/**
	 * Test the products connection on a LocalProductAttribute resolved from a product.
	 */
	public function testLocalProductAttributeToProductsConnection() {
		// Create a product with a local (custom) attribute.
		$local_attr = new \WC_Product_Attribute();
		$local_attr->set_name( 'Engraving' );
		$local_attr->set_options( [ 'yes', 'no' ] );
		$local_attr->set_position( 0 );
		$local_attr->set_visible( true );
		$local_attr->set_variation( false );

		$product_id = $this->factory->product->createSimple(
			[
				'attributes' => [ $local_attr ],
			]
		);

		$other_product = $this->factory->product->createSimple();

		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					... on ProductWithAttributes {
						localAttributes {
							nodes {
								name
								options
								products {
									nodes {
										databaseId
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
		$expected  = [
			$this->expectedNode(
				'product.localAttributes.nodes',
				[
					$this->expectedField( 'name', 'Engraving' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// The local attribute's products connection should include the source product.
		$local_attrs = $this->lodashGet( $response, 'data.product.localAttributes.nodes', [] );
		$engraving_node = null;
		foreach ( $local_attrs as $node ) {
			if ( 'Engraving' === $node['name'] ) {
				$engraving_node = $node;
				break;
			}
		}

		$this->assertNotNull( $engraving_node, 'Engraving attribute should exist.' );
		$product_ids = array_column( $engraving_node['products']['nodes'], 'databaseId' );
		$this->assertContains( $product_id, $product_ids );
		$this->assertNotContains( $other_product, $product_ids );
	}

	/**
	 * Test querying attributes available within a specific product category.
	 */
	public function testProductCategoryToAttributesConnection() {
		$size_attr  = $this->factory->product->createAttribute( 'fit', [ 'slim', 'regular', 'loose' ] );
		$color_attr = $this->factory->product->createAttribute( 'shade', [ 'light', 'dark' ] );

		$clothing_id = $this->factory->product->createProductCategory( 'clothing-attr-test' );
		$food_id     = $this->factory->product->createProductCategory( 'food-attr-test' );

		// Create a product in clothing with 'fit' attribute.
		$clothing_product_id = $this->factory->product->createVariable(
			[
				'category_ids'   => [ $clothing_id ],
				'attribute_data' => [ $size_attr ],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $clothing_product_id,
				'attributes'    => [ 'fit' => 'slim' ],
				'image_id'      => null,
				'regular_price' => 25,
			]
		);

		// Create a product in clothing with 'shade' attribute.
		$clothing_product_id_2 = $this->factory->product->createVariable(
			[
				'category_ids'   => [ $clothing_id ],
				'attribute_data' => [ $color_attr ],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $clothing_product_id_2,
				'attributes'    => [ 'shade' => 'dark' ],
				'image_id'      => null,
				'regular_price' => 30,
			]
		);

		// Create a product in food with NO attributes.
		$this->factory->product->createSimple( [ 'category_ids' => [ $food_id ] ] );

		$this->clearSchema();

		$query = '
			query ($id: ID!) {
				productCategory(id: $id, idType: SLUG) {
					slug
					productAttributes {
						nodes {
							name
							label
							options
							scope
						}
					}
				}
			}
		';

		// Clothing should have both fit and shade attributes.
		$variables = [ 'id' => 'clothing-attr-test' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'productCategory.slug', 'clothing-attr-test' ),
			$this->expectedNode(
				'productCategory.productAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_fit' ),
					$this->expectedField( 'scope', 'GLOBAL' ),
				]
			),
			$this->expectedNode(
				'productCategory.productAttributes.nodes',
				[
					$this->expectedField( 'name', 'pa_shade' ),
					$this->expectedField( 'scope', 'GLOBAL' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Food should have no attributes.
		$variables = [ 'id' => 'food-attr-test' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );
		$attribute_nodes = $this->lodashGet( $response, 'data.productCategory.productAttributes.nodes', [] );
		$this->assertEmpty( $attribute_nodes, 'Food category should have no product attributes.' );
	}
}
