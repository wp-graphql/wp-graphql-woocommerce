<?php

class ProductTagMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test CRUD operations on product tags.
	 *
	 * Covers the fields available in the WooCommerce REST API:
	 * name, slug, description.
	 */
	public function testProductTagCrudMutations() {
		$this->loginAsShopManager();

		// Create.
		$response = $this->graphql(
			[
				'query'     => '
					mutation ($input: CreateProductTagInput!) {
						createProductTag(input: $input) {
							productTag {
								databaseId
								name
								slug
								description
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'name'        => 'Sale Items',
						'slug'        => 'sale-items',
						'description' => 'Products currently on sale',
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'createProductTag.productTag.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createProductTag.productTag.name', 'Sale Items' ),
			$this->expectedField( 'createProductTag.productTag.slug', 'sale-items' ),
			$this->expectedField( 'createProductTag.productTag.description', 'Products currently on sale' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$tag_id = $response['data']['createProductTag']['productTag']['databaseId'];

		// Update.
		$response = $this->graphql(
			[
				'query'     => '
					mutation ($input: UpdateProductTagInput!) {
						updateProductTag(input: $input) {
							productTag {
								databaseId
								name
								slug
								description
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'id'          => $this->toRelayId( 'term', $tag_id ),
						'name'        => 'Clearance',
						'slug'        => 'clearance',
						'description' => 'Clearance items',
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'updateProductTag.productTag.databaseId', $tag_id ),
			$this->expectedField( 'updateProductTag.productTag.name', 'Clearance' ),
			$this->expectedField( 'updateProductTag.productTag.slug', 'clearance' ),
			$this->expectedField( 'updateProductTag.productTag.description', 'Clearance items' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		// Delete.
		$response = $this->graphql(
			[
				'query'     => '
					mutation ($input: DeleteProductTagInput!) {
						deleteProductTag(input: $input) {
							productTag {
								databaseId
								name
							}
						}
					}
				',
				'variables' => [
					'input' => [
						'id' => $this->toRelayId( 'term', $tag_id ),
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'deleteProductTag.productTag.databaseId', $tag_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$this->assertNull( term_exists( $tag_id, 'product_tag' ) );
	}
}
