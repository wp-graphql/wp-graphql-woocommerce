<?php

class ProductTaxonomyQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testProductCategoriesToProductsQuery() {
		// Create categories.
		$clothing_category_id    = $this->factory->product->createProductCategory( 'clothing' );
		$shoes_id                = $this->factory->product->createProductCategory( 'shoes', array( 'parent' => $clothing_category_id ) );
		$accessories_id          = $this->factory->product->createProductCategory( 'accessories', array( 'parent' => $clothing_category_id ) );
		$electronics_category_id = $this->factory->product->createProductCategory( 'electronics' );
		$smartphones_id          = $this->factory->product->createProductCategory( 'smartphones', array( 'parent' => $electronics_category_id ) );
		$laptops_id              = $this->factory->product->createProductCategory( 'laptops', array( 'parent' => $electronics_category_id ) );

		// Create products.
		$clothing_ids    = $this->factory->product->create_many( 5, array( 'category_ids' => array( $clothing_category_id ) ) );
		$shoes_ids       = $this->factory->product->create_many( 5, array( 'category_ids' => array( $shoes_id, $accessories_id ) ) );
		$accessories_ids = $this->factory->product->create_many( 5, array( 'category_ids' => array( $accessories_id ) ) );
		$electronics_ids = $this->factory->product->create_many( 5, array( 'category_ids' => array( $electronics_category_id ) ) );
		$smartphones_ids = $this->factory->product->create_many( 5, array( 'category_ids' => array( $smartphones_id ) ) );
		$laptops_ids     = $this->factory->product->create_many( 5, array( 'category_ids' => array( $laptops_id, $electronics_category_id ) ) );

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

		$variables = array(
			'id' => 'clothing',
		);

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array(
				$this->expectedField( 'productCategory.id', $this->toRelayId( 'term', $clothing_category_id ) ),
				$this->expectedField( 'productCategory.slug', 'clothing' ),
			),
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
				},
				$clothing_ids
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
				},
				array_merge( $smartphones_ids, $shoes_ids, $accessories_ids )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'id' => 'accessories',
		);

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array(
				$this->expectedField( 'productCategory.id', $this->toRelayId( 'term', $accessories_id ) ),
				$this->expectedField( 'productCategory.slug', 'accessories' ),
			),
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
				},
				array_merge( $accessories_ids, $shoes_ids )
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
				},
				array_merge( $clothing_ids, $smartphones_ids, $laptops_ids )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'id' => 'electronics',
		);

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array(
				$this->expectedField( 'productCategory.id', $this->toRelayId( 'term', $electronics_category_id ) ),
				$this->expectedField( 'productCategory.slug', 'electronics' ),
			),
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
				},
				array_merge( $electronics_ids, $laptops_ids )
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productCategory.products.nodes.#.databaseId', $id );
				},
				array_merge( $clothing_ids, $shoes_ids, $accessories_ids, $smartphones_ids )
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
		$tag1_product_ids = $this->factory->product->create_many( 5, array( 'tag_ids' => array( $tag1_id ) ) );
		$tag2_product_ids = $this->factory->product->create_many( 5, array( 'tag_ids' => array( $tag2_id, $tag3_id ) ) );
		$tag3_product_ids = $this->factory->product->create_many( 5, array( 'tag_ids' => array( $tag3_id ) ) );

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

		$variables = array(
			'id' => 'tag1',
		);

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array(
				$this->expectedField( 'productTag.id', $this->toRelayId( 'term', $tag1_id ) ),
				$this->expectedField( 'productTag.slug', 'tag1' ),
			),
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productTag.products.nodes.#.databaseId', $id );
				},
				$tag1_product_ids,
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productTag.products.nodes.#.databaseId', $id );
				},
				array_merge( $tag2_product_ids, $tag3_product_ids )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'id' => 'tag2',
		);

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array(
				$this->expectedField( 'productTag.id', $this->toRelayId( 'term', $tag2_id ) ),
				$this->expectedField( 'productTag.slug', 'tag2' ),
			),
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productTag.products.nodes.#.databaseId', $id );
				},
				$tag2_product_ids,
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productTag.products.nodes.#.databaseId', $id );
				},
				array_merge( $tag1_product_ids, $tag3_product_ids )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'id' => 'tag3',
		);

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array_merge(
			array(
				$this->expectedField( 'productTag.id', $this->toRelayId( 'term', $tag3_id ) ),
				$this->expectedField( 'productTag.slug', 'tag3' ),
			),
			array_map(
				function ( $id ) {
					return $this->expectedField( 'productTag.products.nodes.#.databaseId', $id );
				},
				array_merge( $tag2_product_ids, $tag3_product_ids )
			),
			array_map(
				function ( $id ) {
					return $this->not()->expectedField( 'productTag.products.nodes.#.databaseId', $id );
				},
				$tag1_product_ids,
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
