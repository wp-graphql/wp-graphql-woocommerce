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

		delete_option(
            'woocommerce_stripe_settings',
            array(
                'enabled'                       => 'no',
                'title'                         => 'Credit Card (Stripe)',
                'description'                   => 'Pay with your credit card via Stripe',
                'webhook'                       => '',
                'testmode'                      => 'yes',
                'test_publishable_key'          => defined( 'STRIPE_API_PUBLISHABLE_KEY' )
                    ? STRIPE_API_PUBLISHABLE_KEY
                    : getenv( 'STRIPE_API_PUBLISHABLE_KEY' ),
                'test_secret_key'               => defined( 'STRIPE_API_SECRET_KEY' )
                    ? STRIPE_API_SECRET_KEY
                    : getenv( 'STRIPE_API_SECRET_KEY' ),
                'test_webhook_secret'           => '',
                'publishable_key'               => '',
                'secret_key'                    => '',
                'webhook_secret'                => '',
                'inline_cc_form'                => 'no',
                'statement_descriptor'          => '',
                'capture'                       => 'yes',
                'payment_request'               => 'yes',
                'payment_request_button_type'   => 'buy',
                'payment_request_button_theme'  => 'dark',
                'payment_request_button_height' => '44',
                'saved_cards'                   => 'yes',
                'logging'                       => 'no',
            )
		);

		// Reload gateways.
		\WC()->payment_gateways->init();
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
							'id'     => 'bacs',
							'title'  => 'Direct bank transfer',
							'icon'   => null,
						),
						array(
							'id'     => 'cheque',
							'title'  => 'Check payments',
							'icon'   => null,
						),
						array(
							'id'     => 'cod',
							'title'  => 'Cash on delivery',
							'icon'   => null,
						),
						array(
							'id'     => 'paypal',
							'title'  => 'PayPal',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe',
							'title'  => 'Credit Card (Stripe)',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_sepa',
							'title'  => 'SEPA Direct Debit',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_bancontact',
							'title'  => 'Bancontact',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_sofort',
							'title'  => 'SOFORT',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_giropay',
							'title'  => 'Giropay',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_eps',
							'title'  => 'EPS',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_ideal',
							'title'  => 'iDeal',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_p24',
							'title'  => 'Przelewy24 (P24)',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_alipay',
							'title'  => 'Alipay',
							'icon'   => null,
						),
						array(
							'id'     => 'stripe_multibanco',
							'title'  => 'Multibanco',
							'icon'   => null,
						),
					),
				),
			),
		);

		$this->assertEquals( $expected, $actual );
	}

}
