<?php

class RootQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testShippingCountriesQuery() {
		// Create shipping zones and shipping rates.
		update_option( 'woocommerce_allowed_countries', 'specific' );
		update_option( 'woocommerce_specific_allowed_countries', [ 'US', 'CA' ] );

		// Create query
		$query = '
			query {
				allowedCountries
			}
		';

		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'allowedCountries.#', 'US' ),
			$this->expectedField( 'allowedCountries.#', 'CA' ),
			$this->not()->expectedField( 'allowedCountries.#', 'GB' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testShippingCountryStatesQuery() {
		// Create shipping zones and shipping rates.
		update_option( 'woocommerce_allowed_countries', 'specific' );
		update_option( 'woocommerce_specific_allowed_countries', [ 'US', 'CA' ] );

		// Create query
		$query = '
			query ($country: CountriesEnum!) {
				allowedCountryStates(country: $country) {
					name
					code
				}
			}
		';

		$variables = [ 'country' => 'US' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'allowedCountryStates.#',
				[
					$this->expectedField( 'name', 'Alaska' ),
					$this->expectedField( 'code', 'AL' ),
				]
			),
			$this->expectedObject(
				'allowedCountryStates.#',
				[
					$this->not()->expectedField( 'name', 'Ontario' ),
					$this->not()->expectedField( 'code', 'ON' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		$variables = [ 'country' => 'CA' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'allowedCountryStates.#',
				[
					$this->expectedField( 'name', 'Ontario' ),
					$this->expectedField( 'code', 'ON' ),
				]
			),
			$this->expectedObject(
				'allowedCountryStates.#',
				[
					$this->not()->expectedField( 'name', 'Alaska' ),
					$this->not()->expectedField( 'code', 'AL' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
