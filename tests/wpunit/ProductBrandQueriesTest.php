<?php

class ProductBrandQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Helper: create a product brand term.
	 *
	 * @param string $name      Term name.
	 * @param int    $parent_id Optional parent term ID.
	 *
	 * @return int Term ID.
	 */
	private function createProductBrand( $name, $parent_id = 0 ) {
		$args = [];
		if ( $parent_id ) {
			$args['parent'] = $parent_id;
		}

		$existing = term_exists( $name, 'product_brand' );
		if ( $existing ) {
			return (int) $existing['term_id'];
		}

		$term = wp_insert_term( $name, 'product_brand', $args );
		$this->assertNotWPError( $term, "Failed to create product_brand term: {$name}" );

		return (int) $term['term_id'];
	}

	/**
	 * Helper: create a simple product assigned to the given brand term IDs.
	 *
	 * @param int[] $brand_ids Brand term IDs.
	 *
	 * @return int Product ID.
	 */
	private function createProductWithBrands( array $brand_ids ) {
		$product_id = $this->factory->product->createSimple();
		wp_set_object_terms( $product_id, $brand_ids, 'product_brand' );

		return $product_id;
	}

	/**
	 * Test that the productBrands root query returns brands.
	 */
	public function testProductBrandsQuery() {
		$brand_a = $this->createProductBrand( 'brand-a' );
		$brand_b = $this->createProductBrand( 'brand-b' );

		$query = '
			query {
				productBrands(first: 100) {
					nodes {
						databaseId
						name
						slug
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'productBrands.nodes.#.databaseId', $brand_a ),
			$this->expectedField( 'productBrands.nodes.#.databaseId', $brand_b ),
			$this->expectedField( 'productBrands.nodes.#.slug', 'brand-a' ),
			$this->expectedField( 'productBrands.nodes.#.slug', 'brand-b' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test that a single productBrand can be queried by slug.
	 */
	public function testSingleProductBrandBySlug() {
		$brand_id = $this->createProductBrand( 'nike' );

		$query = '
			query ($id: ID!) {
				productBrand(id: $id, idType: SLUG) {
					databaseId
					name
					slug
				}
			}
		';

		$variables = [ 'id' => 'nike' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'productBrand.databaseId', $brand_id ),
			$this->expectedField( 'productBrand.slug', 'nike' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test the connection from productBrand to products.
	 */
	public function testProductBrandToProductsConnection() {
		$brand_a = $this->createProductBrand( 'acme' );
		$brand_b = $this->createProductBrand( 'globex' );

		$acme_product_ids   = [
			$this->createProductWithBrands( [ $brand_a ] ),
			$this->createProductWithBrands( [ $brand_a ] ),
			$this->createProductWithBrands( [ $brand_a ] ),
		];
		$globex_product_ids = [
			$this->createProductWithBrands( [ $brand_b ] ),
			$this->createProductWithBrands( [ $brand_b ] ),
		];

		$query = '
			query ($id: ID!) {
				productBrand(id: $id, idType: SLUG) {
					databaseId
					slug
					products(first: 100) {
						nodes {
							databaseId
						}
					}
				}
			}
		';

		// Query acme brand — should contain acme products, not globex.
		$variables = [ 'id' => 'acme' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_merge(
			[
				$this->expectedField( 'productBrand.databaseId', $brand_a ),
				$this->expectedField( 'productBrand.slug', 'acme' ),
			],
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productBrand.products.nodes.#.databaseId', $id );
				},
				$acme_product_ids,
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productBrand.products.nodes.#.databaseId', $id );
				},
				$globex_product_ids,
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		// Query globex brand — should contain globex products, not acme.
		$variables = [ 'id' => 'globex' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_merge(
			[
				$this->expectedField( 'productBrand.databaseId', $brand_b ),
				$this->expectedField( 'productBrand.slug', 'globex' ),
			],
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productBrand.products.nodes.#.databaseId', $id );
				},
				$globex_product_ids,
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productBrand.products.nodes.#.databaseId', $id );
				},
				$acme_product_ids,
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test the connection from product to productBrands.
	 */
	public function testProductToProductBrandsConnection() {
		$brand_a = $this->createProductBrand( 'adidas' );
		$brand_b = $this->createProductBrand( 'puma' );
		$brand_c = $this->createProductBrand( 'reebok' );

		// Product with two brands.
		$product_id = $this->createProductWithBrands( [ $brand_a, $brand_b ] );

		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					databaseId
					productBrands {
						nodes {
							databaseId
							slug
						}
					}
				}
			}
		';

		$variables = [ 'id' => $product_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.databaseId', $product_id ),
			$this->expectedField( 'product.productBrands.nodes.#.databaseId', $brand_a ),
			$this->expectedField( 'product.productBrands.nodes.#.databaseId', $brand_b ),
			$this->not()->expectedField( 'product.productBrands.nodes.#.databaseId', $brand_c ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test hierarchical (parent/child) brand queries.
	 */
	public function testHierarchicalProductBrands() {
		$parent_brand = $this->createProductBrand( 'sportswear' );
		$child_a      = $this->createProductBrand( 'running', $parent_brand );
		$child_b      = $this->createProductBrand( 'basketball', $parent_brand );

		// Query top-level brands with children.
		$query = '
			query {
				productBrands(where: { parent: 0 }) {
					nodes {
						databaseId
						slug
						children {
							nodes {
								databaseId
								slug
							}
						}
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'productBrands.nodes.#.slug', 'sportswear' ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Find the sportswear node and verify children.
		$sportswear_node = null;
		foreach ( $response['data']['productBrands']['nodes'] as $node ) {
			if ( 'sportswear' === $node['slug'] ) {
				$sportswear_node = $node;
				break;
			}
		}

		$this->assertNotNull( $sportswear_node, 'sportswear brand should exist in response.' );
		$this->assertCount( 2, $sportswear_node['children']['nodes'], 'sportswear should have 2 children.' );

		$child_slugs = array_column( $sportswear_node['children']['nodes'], 'slug' );
		$this->assertContains( 'running', $child_slugs );
		$this->assertContains( 'basketball', $child_slugs );
	}

	/**
	 * Test filtering products by brand using the where clause.
	 */
	public function testProductsFilteredByBrand() {
		$brand_a = $this->createProductBrand( 'apple' );
		$brand_b = $this->createProductBrand( 'samsung' );

		$apple_ids   = [
			$this->createProductWithBrands( [ $brand_a ] ),
			$this->createProductWithBrands( [ $brand_a ] ),
		];
		$samsung_ids = [
			$this->createProductWithBrands( [ $brand_b ] ),
		];

		$query = '
			query ($brandSlug: String!) {
				products(where: { productBrand: $brandSlug }) {
					nodes {
						databaseId
					}
				}
			}
		';

		$variables = [ 'brandSlug' => 'apple' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_merge(
			array_map(
				function ( $id ) {
					return $this->expectedField( 'products.nodes.#.databaseId', $id );
				},
				$apple_ids,
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'products.nodes.#.databaseId', $id );
				},
				$samsung_ids,
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test filtering products by brand using the taxonomyFilter parameter.
	 */
	public function testProductsFilteredByBrandViaTaxonomyFilter() {
		$brand_a = $this->createProductBrand( 'toyota' );
		$brand_b = $this->createProductBrand( 'honda' );

		$toyota_ids = [
			$this->createProductWithBrands( [ $brand_a ] ),
			$this->createProductWithBrands( [ $brand_a ] ),
		];
		$honda_ids  = [
			$this->createProductWithBrands( [ $brand_b ] ),
		];

		$query = '
			query ($input: ProductTaxonomyInput) {
				products(where: { taxonomyFilter: $input }) {
					nodes {
						databaseId
					}
				}
			}
		';

		$variables = [
			'input' => [
				'filters' => [
					[
						'taxonomy' => 'PRODUCT_BRAND',
						'terms'    => [ 'toyota' ],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array_map(
				function ( $id ) {
					return $this->expectedField( 'products.nodes.#.databaseId', $id );
				},
				$toyota_ids,
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'products.nodes.#.databaseId', $id );
				},
				$honda_ids,
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test CRUD operations on product brands using WPGraphQL core term mutations.
	 */
	public function testProductBrandCrudMutations() {
		$this->loginAsShopManager();

		// Create.
		$create_query = '
			mutation ($input: CreateProductBrandInput!) {
				createProductBrand(input: $input) {
					productBrand {
						databaseId
						name
						slug
					}
				}
			}
		';

		$response = $this->graphql(
			[
				'query'     => $create_query,
				'variables' => [
					'input' => [
						'name' => 'New Brand',
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'createProductBrand.productBrand.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createProductBrand.productBrand.name', 'New Brand' ),
			$this->expectedField( 'createProductBrand.productBrand.slug', 'new-brand' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$brand_id = $response['data']['createProductBrand']['productBrand']['databaseId'];

		// Update.
		$update_query = '
			mutation ($input: UpdateProductBrandInput!) {
				updateProductBrand(input: $input) {
					productBrand {
						databaseId
						name
					}
				}
			}
		';

		$response = $this->graphql(
			[
				'query'     => $update_query,
				'variables' => [
					'input' => [
						'id'   => $this->toRelayId( 'term', $brand_id ),
						'name' => 'Updated Brand',
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'updateProductBrand.productBrand.databaseId', $brand_id ),
			$this->expectedField( 'updateProductBrand.productBrand.name', 'Updated Brand' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		// Delete.
		$delete_query = '
			mutation ($input: DeleteProductBrandInput!) {
				deleteProductBrand(input: $input) {
					productBrand {
						databaseId
						name
					}
				}
			}
		';

		$response = $this->graphql(
			[
				'query'     => $delete_query,
				'variables' => [
					'input' => [
						'id' => $this->toRelayId( 'term', $brand_id ),
					],
				],
			]
		);
		$expected = [
			$this->expectedField( 'deleteProductBrand.productBrand.databaseId', $brand_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$this->assertNull( term_exists( $brand_id, 'product_brand' ) );
	}
}
