<?php

class TaxClassQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testTaxClassesQuery() {
        // Create tax classes.
        $tax_classes = $this->factory->tax_class->create_many( 2 );

        // Prepare the request.
        $query = '{
            taxClasses {
                nodes {
                    name
                    slug
                }
            }
        }';

        // Execute the request.
        $response = $this->graphql( compact( 'query' ) );
        $expected = [
            $this->expectedNode(
                'taxClasses.nodes',
                [
                    $this->expectedField( 'name', $tax_classes[0]['name'] ),
                    $this->expectedField( 'slug', $tax_classes[0]['slug'] ),
                ]
            ),
            $this->expectedNode(
                'taxClasses.nodes',
                [
                    $this->expectedField( 'name', $tax_classes[1]['name'] ),
                    $this->expectedField( 'slug', $tax_classes[1]['slug'] ),
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }
}
