<?php

class ProductTaxonomyQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
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

    public function testProductTagsToProductsQuery() {
        // Create tags.
        $tag1_id = $this->factory->product->createProductTag( 'tag1' );
        $tag2_id = $this->factory->product->createProductTag( 'tag2' );
        $tag3_id = $this->factory->product->createProductTag( 'tag3' );

        // Create products.
        $tag1_product_ids = $this->factory->product->create_many( 5, [ 'tag_ids' => [ $tag1_id ] ] );
        $tag2_product_ids = $this->factory->product->create_many( 5, [ 'tag_ids' => [ $tag2_id, $tag3_id ] ] );
        $tag3_product_ids = $this->factory->product->create_many( 5, [ 'tag_ids' => [ $tag3_id ] ] );

        $query = 'query ($id: ID!) {
            productTag(id: $id idType: SLUG) {
                id
                slug
                products(first: 100) {
                    nodes {
                        databaseId
                        productTags {
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
            'id' => 'tag1'
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = array_merge(
            [
                $this->expectedField( 'productTag.id', $this->toRelayId( 'term', $tag1_id ) ),
                $this->expectedField( 'productTag.slug', 'tag1' ),
            ],
            array_map(
                function( $id ) {
                    return $this->expectedField( 'productTag.products.nodes.#.databaseId', $id );
                },
                $tag1_product_ids,
            ),
            array_map(
                function( $id ) {
                    return $this->not()->expectedField( 'productTag.products.nodes.#.databaseId', $id );
                },
                array_merge( $tag2_product_ids, $tag3_product_ids )
            ),
        );

        $this->assertQuerySuccessful( $response, $expected );

        $variables = [
            'id' => 'tag2'
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = array_merge(
            [
                $this->expectedField( 'productTag.id', $this->toRelayId( 'term', $tag2_id ) ),
                $this->expectedField( 'productTag.slug', 'tag2' ),
            ],
            array_map(
                function( $id ) {
                    return $this->expectedField( 'productTag.products.nodes.#.databaseId', $id );
                },
                $tag2_product_ids,
            ),
            array_map(
                function( $id ) {
                    return $this->not()->expectedField( 'productTag.products.nodes.#.databaseId', $id );
                },
                array_merge( $tag1_product_ids, $tag3_product_ids )
            ),
        );

        $this->assertQuerySuccessful( $response, $expected );

        $variables = [
            'id' => 'tag3'
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = array_merge(
            [
                $this->expectedField( 'productTag.id', $this->toRelayId( 'term', $tag3_id ) ),
                $this->expectedField( 'productTag.slug', 'tag3' ),
            ],
            array_map(
                function( $id ) {
                    return $this->expectedField( 'productTag.products.nodes.#.databaseId', $id );
                },
                array_merge( $tag2_product_ids, $tag3_product_ids )
            ),
            array_map(
                function( $id ) {
                    return $this->not()->expectedField( 'productTag.products.nodes.#.databaseId', $id );
                },
                $tag1_product_ids,
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
}
