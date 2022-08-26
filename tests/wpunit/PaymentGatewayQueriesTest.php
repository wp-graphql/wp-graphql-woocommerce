<?php

class PaymentGatewayQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function setUp(): void {
		// before
		parent::setUp();

		// Enable payment gateway.
		update_option(
			'woocommerce_bacs_settings',
			[
				'enabled'      => 'yes',
				'title'        => 'Direct bank transfer',
				'description'  => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
				'instructions' => 'Instructions that will be added to the thank you page and emails.',
				'account'      => '',
			]
		);

		update_option(
			'woocommerce_cheque_settings',
			[
				'enabled'      => 'no',
				'title'        => 'Check payments',
				'description'  => 'Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.',
				'instructions' => 'Instructions that will be added to the thank you page and emails.',
				'account'      => '',
			]
		);

		delete_option(
			'woocommerce_stripe_settings',
			[
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
			]
		);

		// Reload gateways.
		\WC()->payment_gateways->init();
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
		 * Tests query.
		 */
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedNode(
				'paymentGateways.nodes',
				[
					$this->expectedField( 'id', 'bacs' ),
					$this->expectedField( 'title', 'Direct bank transfer' ),
					$this->expectedField( 'icon', self::IS_NULL ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests query and "all" where argument response, expects errors due lack of capabilities.
		 */
		$variables = [ 'all' => true ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedErrorPath( 'paymentGateways' ),
			$this->expectedField( 'paymentGateways', self::IS_NULL ),
		];

		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests query and "all" where argument response with proper capabilities.
		 */
		$this->loginAsShopManager();
		$variables = [ 'all' => true ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedNode(
				'paymentGateways.nodes',
				[
					$this->expectedField( 'id', 'bacs' ),
					$this->expectedField( 'title', 'Direct bank transfer' ),
					$this->expectedField( 'icon', self::IS_NULL ),
				]
			),
			$this->expectedNode(
				'paymentGateways.nodes',
				[
					$this->expectedField( 'id', 'cheque' ),
					$this->expectedField( 'title', 'Check payments' ),
					$this->expectedField( 'icon', self::IS_NULL ),
				]
			),
			$this->expectedNode(
				'paymentGateways.nodes',
				[
					$this->expectedField( 'id', 'cod' ),
					$this->expectedField( 'title', 'Cash on delivery' ),
					$this->expectedField( 'icon', self::IS_NULL ),
				]
			),
			$this->expectedNode(
				'paymentGateways.nodes',
				[
					$this->expectedField( 'id', 'stripe' ),
					$this->expectedField( 'title', 'Credit Card (Stripe)' ),
					$this->expectedField( 'icon', self::IS_NULL ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

}
