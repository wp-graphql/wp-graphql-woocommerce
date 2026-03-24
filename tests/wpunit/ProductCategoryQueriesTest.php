<?php

class ProductCategoryQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testProductCategoriesToProductsQuery() {
        // Create categories.
		$clothing_category_id = $this->factory->product->createProductCategory( 'clothing' );
		$shoes_id = $this->factory->product->createProductCategory( 'shoes', $clothing_category_id );
        $accessories_id = $this->factory->product->createProductCategory( 'accessories', $clothing_category_id );
        $electronics_category_id = $this->factory->product->createProductCategory( 'electronics' );
        $smartphones_id = $this->factory->product->createProductCategory( 'smartphones', $electronics_category_id );
        $laptops_id = $this->factory->product->createProductCategory( 'laptops', $electronics_category_id );

        // Create products.
        $clothing_ids = $this->factory->product->create_many( 5, [ 'category_ids' => [ $clothing_category_id ] ] );
        $shoes_ids    = $this->factory->product->create_many( 5, [ 'category_ids' => [ $shoes_id, $accessories_id ] ] );
        $accessories_ids = $this->factory->product->create_many( 5, [ 'category_ids' => [ $accessories_id ] ] );
        $electronics_ids = $this->factory->product->create_many( 5, [ 'category_ids' => [ $electronics_category_id ] ] );
        $smartphones_ids = $this->factory->product->create_many( 5, [ 'category_ids' => [ $smartphones_id ] ] );
        $laptops_ids = $this->factory->product->create_many( 5, [ 'category_ids' => [ $laptops_id, $electronics_category_id ] ] );

        $query = 'query ($id: ID!) {
            productCategory(id: $id idType: SLUG) {
                id
                slug
                products(first: 100) {
                    nodes {
                        databaseId
                        productCategories {
                            nodes {
                                id
                                slug
                            }
                        }
                    }
                }
            }
        }';

        $variables = [
            'id' => 'clothing'
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = array_merge(
            [
                $this->expectedField( 'productCategory.id', $this->toRelayId( 'term', $clothing_category_id ) ),
                $this->expectedField( 'productCategory.slug', 'clothing' ),
            ],
            // Clothing products should appear.
            array_map(
                function( $id ) {
                    return $this->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
                },
                $clothing_ids
            ),
            // Products from non-clothing categories should not appear.
            array_map(
                function( $id ) {
                    return $this->not()->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
                },
                array_merge( $smartphones_ids, $electronics_ids, $laptops_ids )
            ),
        );

        $this->assertQuerySuccessful( $response, $expected );

        $variables = [
            'id' => 'accessories'
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = array_merge(
            [
                $this->expectedField( 'productCategory.id', $this->toRelayId( 'term', $accessories_id ) ),
                $this->expectedField( 'productCategory.slug', 'accessories' ),
            ],
            array_map(
                function( $id ) {
                    return $this->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
                },
                array_merge( $accessories_ids, $shoes_ids )
            ),
            array_map(
                function( $id ) {
                    return $this->not()->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
                },
                array_merge( $clothing_ids, $smartphones_ids, $laptops_ids )
            ),
        );

        $this->assertQuerySuccessful( $response, $expected );

        $variables = [
            'id' => 'electronics'
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = array_merge(
            [
                $this->expectedField( 'productCategory.id', $this->toRelayId( 'term', $electronics_category_id ) ),
                $this->expectedField( 'productCategory.slug', 'electronics' ),
            ],
            // Electronics and its children's products should appear.
            array_map(
                function( $id ) {
                    return $this->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
                },
                array_merge( $electronics_ids, $laptops_ids )
            ),
            // Products from non-electronics categories should not appear.
            array_map(
                function( $id ) {
                    return $this->not()->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
                },
                array_merge( $clothing_ids, $shoes_ids, $accessories_ids )
            ),
        );

        $this->assertQuerySuccessful( $response, $expected );
    }

	public function testProductCategoryChildrenConnection() {
		// Create parent categories.
		$parent_a = $this->factory->product->createProductCategory( 'parent-a' );
		$parent_b = $this->factory->product->createProductCategory( 'parent-b' );

		// Create child categories.
		$child_a1 = $this->factory->product->createProductCategory( 'child-a1', $parent_a );
		$child_a2 = $this->factory->product->createProductCategory( 'child-a2', $parent_a );
		$child_b1 = $this->factory->product->createProductCategory( 'child-b1', $parent_b );

		$query = '
			query GetProductCategories {
				productCategories(where: {parent: 0}) {
					nodes {
						slug
						name
						children {
							nodes {
								name
								slug
							}
						}
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$expected = [
			$this->expectedField( 'productCategories.nodes.#.slug', 'parent-a' ),
			$this->expectedField( 'productCategories.nodes.#.slug', 'parent-b' ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Verify parent-a has its children.
		$parent_a_node = null;
		foreach ( $response['data']['productCategories']['nodes'] as $node ) {
			if ( 'parent-a' === $node['slug'] ) {
				$parent_a_node = $node;
				break;
			}
		}

		$this->assertNotNull( $parent_a_node, 'parent-a category should exist in response.' );
		$this->assertCount( 2, $parent_a_node['children']['nodes'], 'parent-a should have 2 children.' );

		$child_slugs = array_column( $parent_a_node['children']['nodes'], 'slug' );
		$this->assertContains( 'child-a1', $child_slugs );
		$this->assertContains( 'child-a2', $child_slugs );

		// Verify parent-b has its child.
		$parent_b_node = null;
		foreach ( $response['data']['productCategories']['nodes'] as $node ) {
			if ( 'parent-b' === $node['slug'] ) {
				$parent_b_node = $node;
				break;
			}
		}

		$this->assertNotNull( $parent_b_node, 'parent-b category should exist in response.' );
		$this->assertCount( 1, $parent_b_node['children']['nodes'], 'parent-b should have 1 child.' );
		$this->assertEquals( 'child-b1', $parent_b_node['children']['nodes'][0]['slug'] );
	}

	/**
	 * Test that productCategories resolves correctly when term_id and term_taxonomy_id differ.
	 *
	 * This happens when a WP category and a product_cat share the same name,
	 * causing WordPress to reuse the term_id but assign a different term_taxonomy_id.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/597
	 */
	public function testProductCategoriesWithMismatchedTermTaxonomyIds() {
		// Create a term directly in the wp_terms table, then add term_taxonomy
		// entries for both 'category' and 'product_cat' to simulate the scenario
		// where term_id is shared but term_taxonomy_id differs.
		global $wpdb;

		// Insert a shared term.
		$wpdb->insert(
			$wpdb->terms,
			[
				'name'       => 'Electronics',
				'slug'       => 'electronics-shared',
				'term_group' => 0,
			]
		);
		$shared_term_id = (int) $wpdb->insert_id;

		// Add term_taxonomy entry for 'category'.
		$wpdb->insert(
			$wpdb->term_taxonomy,
			[
				'term_id'     => $shared_term_id,
				'taxonomy'    => 'category',
				'description' => '',
				'parent'      => 0,
				'count'       => 0,
			]
		);
		$wp_cat_tt_id = (int) $wpdb->insert_id;

		// Add term_taxonomy entry for 'product_cat'.
		$wpdb->insert(
			$wpdb->term_taxonomy,
			[
				'term_id'     => $shared_term_id,
				'taxonomy'    => 'product_cat',
				'description' => '',
				'parent'      => 0,
				'count'       => 0,
			]
		);
		$product_cat_tt_id = (int) $wpdb->insert_id;

		// Confirm term_taxonomy_ids differ.
		$this->assertNotEquals( $wp_cat_tt_id, $product_cat_tt_id );


		// Clean term caches.
		clean_term_cache( $shared_term_id, 'product_cat' );
		clean_term_cache( $shared_term_id, 'category' );

		// Create a product in this category.
		$product_id = $this->factory->product->createSimple(
			[ 'category_ids' => [ $shared_term_id ] ]
		);

		$query = '
			query ($id: ID!) {
				product(id: $id, idType: DATABASE_ID) {
					databaseId
					productCategories {
						nodes {
							databaseId
							name
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
			$this->expectedNode(
				'product.productCategories.nodes',
				[
					$this->expectedField( 'databaseId', $shared_term_id ),
					$this->expectedField( 'slug', 'electronics-shared' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
