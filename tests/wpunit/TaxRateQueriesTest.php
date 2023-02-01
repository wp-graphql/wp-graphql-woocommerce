<?php

class TaxRateQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function expectedTaxRateData( $rate_id ) {
		$rate = $this->factory->tax_rate->get_object_by_id( $rate_id );

		$expected = [
			$this->expectedField( 'taxRate.id', $this->toRelayId( 'tax_rate', $rate_id ) ),
			$this->expectedField( 'taxRate.databaseId', absint( $rate->tax_rate_id ) ),
			$this->expectedField( 'taxRate.country', ! empty( $rate->tax_rate_country ) ? $rate->tax_rate_country : self::IS_NULL ),
			$this->expectedField( 'taxRate.state', ! empty( $rate->tax_rate_state ) ? $rate->tax_rate_state : self::IS_NULL ),
			$this->expectedField( 'taxRate.postcode', ! empty( $rate->tax_rate_postcode ) ? $rate->tax_rate_postcode : [ '*' ] ),
			$this->expectedField( 'taxRate.city', ! empty( $rate->tax_rate_city ) ? $rate->tax_rate_city : [ '*' ] ),
			$this->expectedField( 'taxRate.rate', ! empty( $rate->tax_rate ) ? $rate->tax_rate : self::IS_NULL ),
			$this->expectedField( 'taxRate.name', ! empty( $rate->tax_rate_name ) ? $rate->tax_rate_name : self::IS_NULL ),
			$this->expectedField( 'taxRate.priority', absint( $rate->tax_rate_priority ) ),
			$this->expectedField( 'taxRate.compound', (bool) $rate->tax_rate_compound ),
			$this->expectedField( 'taxRate.shipping', (bool) $rate->tax_rate_shipping ),
			$this->expectedField( 'taxRate.order', absint( $rate->tax_rate_order ) ),
			$this->expectedField(
				'taxRate.class',
				! empty( $rate->tax_rate_class )
					? WPEnumType::get_safe_name( $rate->tax_rate_class )
					: 'STANDARD'
			),
		];

		return $expected;
	}

	// tests
	public function testTaxQuery() {
		$rate = $this->factory->tax_rate->create();

		$query = '
			query taxRateQuery( $id: ID, $idType: TaxRateIdTypeEnum ) {
				taxRate( id: $id, idType: $idType ) {
					id
					databaseId
					country
					state
					postcode
					city
					rate
					name
					priority
					compound
					shipping
					order
					class
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Tests query, "id" query arg, and results
		 */
		$variables = [ 'id' => $this->toRelayId( 'tax_rate', $rate ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = $this->expectedTaxRateData( $rate );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests query, "rateId" query arg, and results
		 */
		$variables = [
			'id'     => $rate,
			'idType' => 'DATABASE_ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testTaxesQuery() {
		$rates = [
			$this->factory->tax_rate->create(),
			$this->factory->tax_rate->create(
				[
					'country'  => 'US',
					'state'    => 'AL',
					'city'     => 'Montgomery',
					'postcode' => '12345; 123456;',
					'rate'     => '10.5',
					'name'     => 'US AL',
					'priority' => '1',
					'compound' => '1',
					'shipping' => '1',
					'class'    => 'reduced-rate',
				]
			),
			$this->factory->tax_rate->create(
				[
					'country'  => 'US',
					'state'    => 'VA',
					'city'     => 'Norfolk',
					'postcode' => '23451;',
					'rate'     => '10.5',
					'name'     => 'US VA',
					'priority' => '1',
					'compound' => '1',
					'shipping' => '1',
					'class'    => 'zero-rate',
				]
			),
		];

		$query = '
			query ( $class: TaxClassEnum, $postCode: String, $postCodeIn: [String] ) {
				taxRates( where: { class: $class, postCode: $postCode, postCodeIn: $postCodeIn } ) {
					nodes {
						id
						class
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Tests query
		 */
		$response = $this->graphql( compact( 'query' ) );
		$expected = array_map(
			function( $id ) {
				return $this->expectedNode(
					'taxRates.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'tax_rate', $id ) ),
					]
				);
			},
			$rates
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests "class" where arg
		 */
		$reduced_tax_rates = array_values(
			array_filter(
				$rates,
				function( $id ) {
					$rate = $this->factory->tax_rate->get_object_by_id( $id );
					return 'reduced-rate' === $rate->tax_rate_class;
				}
			)
		);
		$variables         = [ 'class' => 'REDUCED_RATE' ];
		$response          = $this->graphql( compact( 'query', 'variables' ) );
		$expected          = array_map(
			function( $id ) {
				return $this->expectedNode(
					'taxRates.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'tax_rate', $id ) ),
					]
				);
			},
			$reduced_tax_rates,
		);

		$expected[] = $this->not()->expectedField( 'taxRates.nodes.#.class', 'STANDARD' );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests "postCode" where arg
		 */
		$variables = [ 'postCode' => '23451' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'taxRates.nodes.0.id', $this->toRelayId( 'tax_rate', $rates[2] ) ),
			$this->not()->expectedField( 'taxRates.nodes.#.id', $this->toRelayId( 'tax_rate', $rates[0] ) ),
			$this->not()->expectedField( 'taxRates.nodes.#.id', $this->toRelayId( 'tax_rate', $rates[1] ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Tests "postCodeIn" where arg
		 */
		$variables = [ 'postCodeIn' => [ '123456', '23451' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'taxRates.nodes.#.id', $this->toRelayId( 'tax_rate', $rates[1] ) ),
			$this->expectedField( 'taxRates.nodes.#.id', $this->toRelayId( 'tax_rate', $rates[2] ) ),
			$this->not()->expectedField( 'taxRates.nodes.#.id', $this->toRelayId( 'tax_rate', $rates[0] ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testTaxIncludedOptionEffect() {
		// Create tax rate.
		$this->factory->tax_rate->create(
			[
				'country'  => 'US',
				'state'    => '',
				'rate'     => 10,
				'name'     => 'US',
				'priority' => '1',
				'compound' => '0',
				'shipping' => '1',
				'class'    => '',
			]
		);

		// Set customer address.
		\WC()->customer->set_shipping_city( 'Norfolk' );
		\WC()->customer->set_shipping_state( 'VA' );
		\WC()->customer->set_shipping_postcode( '23451' );
		\WC()->customer->set_shipping_country( 'US' );
		\WC()->customer->save();
		\WC()->initialize_session();

		// Create product to query.
		$product_id = $this->factory->product->createSimple(
			[
				'price'         => 10,
				'regular_price' => 10,
			]
		);
		$query      = '
			query( $id: ID! ) {
				product( id: $id ) {
					... on SimpleProduct {
						price
						regularPrice
					}
				}
			}
		';
		$variables  = [ 'id' => $this->toRelayId( 'post', $product_id ) ];

		/**
		 * Assertion One
		 *
		 * Test without taxes included.
		 */

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'product.price', '$10.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Clear product cache.
		$this->clearLoaderCache( 'wc_post' );
		$this->clearLoaderCache( 'post' );

		/**
		 * Assertion Two
		 *
		 * Test with taxes included.
		 */
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_tax_display_shop', 'incl' );

		$this->logData( \wc_prices_include_tax() ? 'included' : 'excluded' );
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'product.price', '$11.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
