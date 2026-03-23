<?php

class ProductTagQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
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
}
