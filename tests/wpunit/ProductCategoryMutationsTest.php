<?php

class ProductCategoryMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test CRUD operations on product categories.
	 *
	 * Covers the fields available in the WooCommerce REST API:
	 * name, slug, parent, description, display, image, menu_order.
	 */
	public function testProductCategoryCrudMutations() {
		$this->loginAsShopManager();

		// Create an image attachment for use in the category.
		$image_id = $this->factory->post->create(
			[
				'post_status' => 'publish',
				'post_title'  => 'Category Image',
				'post_type'   => 'attachment',
			]
		);

		// Create a parent category with all supported fields.
		$response = $this->graphql(
			[
				'query'     => '
					mutation ($input: CreateProductCategoryInput!) {
						createProductCategory(input: $input) {
							productCategory {
								databaseId
								name
								slug
								description
								parentDatabaseId
								display
								menuOrder
								image {
									databaseId
								}
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'name'        => 'Electronics',
						'slug'        => 'electronics',
						'description' => 'All electronic products',
						'display'     => 'BOTH',
						'menuOrder'   => 5,
						'imageId'     => $image_id,
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'createProductCategory.productCategory.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createProductCategory.productCategory.name', 'Electronics' ),
			$this->expectedField( 'createProductCategory.productCategory.slug', 'electronics' ),
			$this->expectedField( 'createProductCategory.productCategory.description', 'All electronic products' ),
			$this->expectedField( 'createProductCategory.productCategory.parentDatabaseId', self::IS_NULL ),
			$this->expectedField( 'createProductCategory.productCategory.display', 'BOTH' ),
			$this->expectedField( 'createProductCategory.productCategory.menuOrder', 5 ),
			$this->expectedField( 'createProductCategory.productCategory.image.databaseId', $image_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$parent_id = $response['data']['createProductCategory']['productCategory']['databaseId'];

		// Create a child category with parent.
		$response = $this->graphql(
			[
				'query'     => '
					mutation ($input: CreateProductCategoryInput!) {
						createProductCategory(input: $input) {
							productCategory {
								databaseId
								name
								slug
								description
								parentDatabaseId
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'name'        => 'Smartphones',
						'slug'        => 'smartphones',
						'description' => 'Mobile phones and accessories',
						'parentId'    => $this->toRelayId( 'term', $parent_id ),
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'createProductCategory.productCategory.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createProductCategory.productCategory.name', 'Smartphones' ),
			$this->expectedField( 'createProductCategory.productCategory.slug', 'smartphones' ),
			$this->expectedField( 'createProductCategory.productCategory.description', 'Mobile phones and accessories' ),
			$this->expectedField( 'createProductCategory.productCategory.parentDatabaseId', $parent_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$child_id = $response['data']['createProductCategory']['productCategory']['databaseId'];

		// Update the child category with new display and menu_order.
		$update_image_id = $this->factory->post->create(
			[
				'post_status' => 'publish',
				'post_title'  => 'Updated Category Image',
				'post_type'   => 'attachment',
			]
		);
		$response        = $this->graphql(
			[
				'query'     => '
					mutation ($input: UpdateProductCategoryInput!) {
						updateProductCategory(input: $input) {
							productCategory {
								databaseId
								name
								slug
								description
								display
								menuOrder
								image {
									databaseId
								}
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'id'          => $this->toRelayId( 'term', $child_id ),
						'name'        => 'Mobile Phones',
						'slug'        => 'mobile-phones',
						'description' => 'Updated description',
						'display'     => 'SUBCATEGORIES',
						'menuOrder'   => 10,
						'imageId'     => $update_image_id,
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'updateProductCategory.productCategory.databaseId', $child_id ),
			$this->expectedField( 'updateProductCategory.productCategory.name', 'Mobile Phones' ),
			$this->expectedField( 'updateProductCategory.productCategory.slug', 'mobile-phones' ),
			$this->expectedField( 'updateProductCategory.productCategory.description', 'Updated description' ),
			$this->expectedField( 'updateProductCategory.productCategory.display', 'SUBCATEGORIES' ),
			$this->expectedField( 'updateProductCategory.productCategory.menuOrder', 10 ),
			$this->expectedField( 'updateProductCategory.productCategory.image.databaseId', $update_image_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		// Delete the child category.
		$response = $this->graphql(
			[
				'query'     => '
					mutation ($input: DeleteProductCategoryInput!) {
						deleteProductCategory(input: $input) {
							productCategory {
								databaseId
								name
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'id' => $this->toRelayId( 'term', $child_id ),
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'deleteProductCategory.productCategory.databaseId', $child_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$this->assertNull( term_exists( $child_id, 'product_cat' ) );
	}
}
