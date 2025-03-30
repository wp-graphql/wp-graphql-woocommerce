<?php

use WPGraphQL\Type\WPEnumType;

class TaxRateMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateTaxRateMutation() {
        // Create tax class.
        $tax_class = $this->factory->tax_class->create();

        // Prepare the request.
        $query = 'mutation ($input: CreateTaxRateInput!) {
            createTaxRate(input: $input) {
                taxRate {
                    id
                    rate
                    country
                    state
                    postcode
                    city
                    postcodes
                    cities
                    priority
                    compound
                    shipping
                    order
                    class
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'rate'      => '10',
                'country'   => 'US',
                'state'     => 'CA',
                'postcodes' => [ '12345', '67890' ],
                'cities'    => [ 'Los Angeles', 'San Francisco' ],
                'priority'  => 1,
                'compound'  => false,
                'shipping'  => false,
                'order'     => 0,
                'class'     => WPEnumType::get_safe_name( $tax_class['slug'] )
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
                'createTaxRate.taxRate',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'rate', static::NOT_FALSY ),
                    $this->expectedField( 'country', 'US' ),
                    $this->expectedField( 'state', 'CA' ),
                    $this->expectedField( 'postcode', '67890' ),
                    $this->expectedField( 'postcodes.0', '12345' ),
                    $this->expectedField( 'postcodes.1', '67890' ),
                    $this->expectedField( 'city', 'SAN FRANCISCO' ),
                    $this->expectedField( 'cities.0', 'LOS ANGELES' ),
                    $this->expectedField( 'cities.1', 'SAN FRANCISCO' ),
                    $this->expectedField( 'priority', 1 ),
                    $this->expectedField( 'compound', false ),
                    $this->expectedField( 'shipping', false ),
                    $this->expectedField( 'order', 0 ),
                    $this->expectedField( 'class', WPEnumType::get_safe_name( $tax_class['slug'] ) )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateTaxRateMutation() {
        // Create a tax class.
        $old_tax_class = $this->factory->tax_class->create();
        $new_tax_class = $this->factory->tax_class->create();

        // Create a tax rate.
        $tax_rate_id = $this->factory->tax_rate->create( [ 'class' => $old_tax_class['slug'] ] );

        // Prepare the request.
        $query = 'mutation ($input: UpdateTaxRateInput!) {
            updateTaxRate(input: $input) {
                taxRate {
                    id
                    rate
                    country
                    state
                    postcodes
                    cities
                    priority
                    compound
                    shipping
                    order
                    class
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'id'        => $tax_rate_id,
                'rate'      => '20',
                'country'   => 'US',
                'state'     => 'NY',
                'postcodes' => [ '54321', '09876' ],
                'cities'    => [ 'New York', 'Buffalo' ],
                'priority'  => 2,
                'compound'  => true,
                'shipping'  => true,
                'order'     => 1,
                'class'     => WPEnumType::get_safe_name( $new_tax_class['slug'] )
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
                'updateTaxRate.taxRate',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'tax_rate', $tax_rate_id ) ),
                    $this->expectedField( 'rate', static::NOT_FALSY ),
                    $this->expectedField( 'country', 'US' ),
                    $this->expectedField( 'state', 'NY' ),
                    $this->expectedField( 'postcodes.0', '54321' ),
                    $this->expectedField( 'postcodes.1', '09876' ),
                    $this->expectedField( 'cities.0', 'NEW YORK' ),
                    $this->expectedField( 'cities.1', 'BUFFALO' ),
                    $this->expectedField( 'priority', 2 ),
                    $this->expectedField( 'compound', true ),
                    $this->expectedField( 'shipping', true ),
                    $this->expectedField( 'order', 1 ),
                    $this->expectedField( 'class', WPEnumType::get_safe_name( $new_tax_class['slug'] ) ),
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteTaxRateMutation() {
        // Create a tax rate.
        $tax_rate = $this->factory->tax_rate->create_and_get(
            [
                'rate'    => '30',
                'country' => 'US',
                'state'   => 'TX',
                'class'   => 'zero-rate',
            ]
        );

        // Prepare the request.
        $query = 'mutation ($input: DeleteTaxRateInput!) {
            deleteTaxRate(input: $input) {
                taxRate {
                    id
                    rate
                    country
                    state
                    class
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'id' => absint( $tax_rate->tax_rate_id ),
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
                'deleteTaxRate.taxRate',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'tax_rate', $tax_rate->tax_rate_id ) ),
                    $this->expectedField( 'rate', static::NOT_FALSY ),
                    $this->expectedField( 'country', 'US' ),
                    $this->expectedField( 'state', 'TX' ),
                    $this->expectedField( 'class', 'ZERO_RATE' ),
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );

        // Ensure the tax rate has been deleted.
        $tax_rate = $this->factory->tax_rate->get_object_by_id( $tax_rate->tax_rate_id );
        $this->assertNull( $tax_rate );
    }
}
