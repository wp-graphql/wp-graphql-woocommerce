<?php

class RootQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testShippingCountriesQuery() {
        // Create shipping zones and shipping rates.
        update_option( 'woocommerce_allowed_countries', 'specific' );
		update_option( 'woocommerce_specific_allowed_countries', array( 'US', 'CA' ) );

        // Create query
        $query = '
            query {
                shippingCountries
            }
        ';

        $response = $this->graphql( compact( 'query' ) );
        $expected = [
            $this->expectedField( 'shippingCountries.#', 'US' ),
            $this->expectedField( 'shippingCountries.#', 'CA' ),
            $this->not()->expectedField( 'shippingCountries.#', 'GB' ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testShippingCountryStatesQuery() {
        // Create shipping zones and shipping rates.
        update_option( 'woocommerce_allowed_countries', 'specific' );
        update_option( 'woocommerce_specific_allowed_countries', ['US', 'CA' ] );

        // Create query
        $query = '
            query {
                shippingCountryStates(country: US){
                    name
                    code
                }
            }
        ';

        $response = $this->graphql( compact( 'query' ) );
        $expected = [
            $this->expectedObject(
                'shippingCountryStates.#',
                [
                    $this->expectedField( 'name', 'Alaska' ),
                    $this->expectedField( 'code', 'AL' ),
                ]
            ),
            $this->expectedObject(
                'shippingCountryStates.#',
                [
                    $this->not()->expectedField( 'name', 'Ontario' ),
                    $this->not()->expectedField( 'code', 'ON' ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );

        // Create query
        $query = '
            query {
                shippingCountryStates(country: CA){
                    name
                    code
                }
            }
        ';

        $response = $this->graphql( compact( 'query' ) );
        $expected = [
            $this->expectedObject(
                'shippingCountryStates.#',
                [
                    $this->expectedField( 'name', 'Ontario' ),
                    $this->expectedField( 'code', 'ON' ),
                ]
            ),
            $this->expectedObject(
                'shippingCountryStates.#',
                [
                    $this->not()->expectedField( 'name', 'Alaska' ),
                    $this->not()->expectedField( 'code', 'AL' ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }
}
