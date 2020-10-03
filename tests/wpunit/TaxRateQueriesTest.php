<?php

use GraphQLRelay\Relay;

class TaxRateQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $rate;
	private $helper;

	public function setUp() {
		parent::setUp();

		$this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->helper       = $this->getModule('\Helper\Wpunit')->tax_rate();
		$this->rate         = $this->helper->create();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testTaxQuery() {
		$id = Relay::toGlobalId( 'tax_rate', $this->rate );

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
		 * tests query, "id" query arg, and results
		 */
		$variables = array( 'id' => $id );
		$actual = do_graphql_request( $query, 'taxRateQuery', $variables );
		$expected = array( 'data' => array( 'taxRate' => $this->helper->print_query( $this->rate ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * tests query, "rateId" query arg, and results
		 */
		$variables = array(
			'id'     => $this->rate,
			'idType' => 'DATABASE_ID',
		);
		$actual = do_graphql_request( $query, 'taxRateQuery', $variables );
		$expected = array( 'data' => array( 'taxRate' => $this->helper->print_query( $this->rate ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testTaxesQuery() {
		$rates = array(
			$this->rate,
			$this->helper->create(
				array(
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
				)
			),
			$this->helper->create(
				array(
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
				)
			),
		);

		$query = '
			query ( $class: TaxClassEnum, $postCode: String, $postCodeIn: [String] ) {
				taxRates( where: { class: $class, postCode: $postCode, postCodeIn: $postCodeIn } ) {
					nodes {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * tests query
		 */
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array(
			'data' => array(
				'taxRates' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'tax_rate', $id ) );
						},
						$rates
					)
				)
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * tests "class" where arg
		 */
		$variables = array( 'class' => 'REDUCED_RATE' );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'taxRates' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'tax_rate', $id ) );
						},
						array_values(
							array_filter(
								$rates,
								function( $id ) {
									$rate = $this->helper->get_rate_object( $id );
									return 'reduced-rate' === $rate->tax_rate_class;
								}
							)
						)
					)
				)
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * tests "postCode" where arg
		 */
		$variables = array( 'postCode' => '23451' );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'taxRates' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'tax_rate', $id ) );
						},
						array( $rates[2] )
					)
				)
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Four
		 *
		 * tests "postCodeIn" where arg
		 */
		$variables = array( 'postCodeIn' => array( '123456', '23451' ) );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'taxRates' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'tax_rate', $id ) );
						},
						array( $rates[1],$rates[2] )
					)
				)
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
