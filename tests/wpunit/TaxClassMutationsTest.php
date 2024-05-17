<?php

class TaxClassMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateTaxClassMutation() {
        // Prepare the request.
        $query = 'mutation ($input: CreateTaxClassInput!) {
            createTaxClass(input: $input) {
                taxClass {
                    name
                    slug
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'name' => 'Test Tax Class',
                'slug' => 'test-tax-class'
            ]
        ];

        // Execute the request expecting failure due to missing permissions.
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Login as shop manager.
        $this->loginAsShopManager();

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'createTaxClass.taxClass',
                [
                    $this->expectedField( 'name', 'Test Tax Class' ),
                    $this->expectedField( 'slug', 'test-tax-class' )
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteTaxClassMutation() {
        // Create a tax class.
        $tax_class = $this->factory->tax_class->create();

        // Prepare the request.
        $query = 'mutation ($input: DeleteTaxClassInput!) {
            deleteTaxClass(input: $input) {
                taxClass {
                    name
                    slug
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'slug' => $tax_class['slug']
            ]
        ];

        // Execute the request expecting failure due to missing permissions.
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Login as shop manager.
        $this->loginAsShopManager();

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'deleteTaxClass.taxClass',
                [
                    $this->expectedField( 'name', $tax_class['name'] ),
                    $this->expectedField( 'slug', $tax_class['slug'] )
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );

        // Ensure the tax class was deleted.
        $tax_class = $this->factory->tax_class->get_object_by_id( $tax_class['slug'] );
        $this->assertNull( $tax_class );
    }
}
