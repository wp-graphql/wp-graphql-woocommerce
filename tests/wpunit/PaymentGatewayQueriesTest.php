<?php

class PaymentGatewayQueriesTest extends \Codeception\TestCase\WPTestCase
{

	public function setUp() {
		// before
		parent::setUp();

		// Create users.
		$this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );

		// Enable payment gateway.
		update_option(
			'woocommerce_bacs_settings',
			array(
				'enabled'      => 'yes',
				'title'        => 'Direct bank transfer',
				'description'  => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
				'instructions' => 'Instructions that will be added to the thank you page and emails.',
				'account'      => '',
			)
		);

		update_option(
			'woocommerce_cheque_settings',
			array(
				'enabled'      => 'no',
				'title'        => 'Check payments',
				'description'  => 'Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.',
				'instructions' => 'Instructions that will be added to the thank you page and emails.',
				'account'      => '',
			)
		);
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	// tests
	public function testPaymentGatewaysQueryAndWhereArgs() {
		$query = '
			query ( $all: Boolean ) {
				paymentGateways(where:{ all: $all }) {
					nodes {
						id
						title
						description
						icon
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * tests query.
		 */
		$actual   = graphql( array( 'query' => $query ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$expected = array(
			'data' => array(
				'paymentGateways' => array(
					'nodes' => array(
						array(
							'id'          => 'bacs',
							'title'       => 'Direct bank transfer',
							'description' => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
							'icon'        => null,
						),
					),
				),
			),
		);

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * tests query and "all" where argument response, expects errors due lack of capabilities.
		 */
		$variables = array( 'all' => true );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'errors', $actual );

		/**
		 * Assertion Three
		 *
		 * tests query and "all" where argument response with proper capabilities.
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'all' => true );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$expected = array(
			'data' => array(
				'paymentGateways' => array(
					'nodes' => array(
						array(
							'id'          => 'bacs',
							'title'       => 'Direct bank transfer',
							'description' => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
							'icon'        => null,
						),
						array(
							'id'          => 'cheque',
							'title'       => 'Check payments',
							'description' => 'Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.',
							'icon'        => null,
						),
						array(
							'id'          => 'cod',
							'title'       => 'Cash on delivery',
							'description' => 'Pay with cash upon delivery.',
							'icon'        => null,
						),
						array(
							'id'          => 'paypal',
							'title'       => 'PayPal',
							'description' => 'Pay via PayPal; you can pay with your credit card if you don\'t have a PayPal account.',
							'icon'        => null,
						),
					),
				),
			),
		);

		$this->assertEquals( $expected, $actual );
	}

}