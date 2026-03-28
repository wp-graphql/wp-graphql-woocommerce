<?php

class WooCommerceEmailTemplatesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		parent::setUp();

		// Reset captured emails from previous tests.
		reset_phpmailer_instance();

		// Enable bacs payment gateway.
		$gateways     = \WC()->payment_gateways->payment_gateways();
		$bacs_gateway = $gateways['bacs'];
		$bacs_gateway->settings['enabled'] = 'yes';
		update_option( $bacs_gateway->get_option_key(), $bacs_gateway->settings );
		\WC()->payment_gateways->init();
	}

	public function tearDown(): void {
		// Reset WC state to prevent test contamination.
		\WC()->customer = null;
		\WC()->session  = null;
		\WC()->cart     = null;
		\WC()->initialize_session();
		\WC()->initialize_cart();

		$this->loginAs( 0 );

		parent::tearDown();
	}

	/**
	 * Find a sent email by recipient address in the mock mailer.
	 *
	 * @param string $to_address Recipient email address.
	 *
	 * @return object|null
	 */
	private function find_sent_email( $to_address ) {
		$mailer = tests_retrieve_phpmailer_instance();
		if ( ! $mailer ) {
			return null;
		}

		foreach ( $mailer->mock_sent as $index => $sent ) {
			$recipient = $mailer->get_recipient( 'to', $index );
			if ( $recipient && $to_address === $recipient->address ) {
				return $mailer->get_sent( $index );
			}
		}

		return null;
	}

	public function testRegisterCustomerSendsWooCommerceNewAccountEmail() {
		$query = '
			mutation ($input: RegisterCustomerInput!) {
				registerCustomer(input: $input) {
					customer {
						databaseId
						email
					}
				}
			}
		';

		$variables = [
			'input' => [
				'email'        => 'newcustomer@example.com',
				'username'     => 'newcustomer',
				'password'     => 'testpassword123',
				'authenticate' => true,
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'registerCustomer.customer.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'registerCustomer.customer.email', 'newcustomer@example.com' ),
			]
		);

		$sent = $this->find_sent_email( 'newcustomer@example.com' );
		$this->assertNotNull( $sent, 'WC new account email should be sent to the registered customer.' );
		$this->assertStringContainsString( 'text/html', $sent->header, 'New account email should use HTML content type from WooCommerce template.' );
	}

	public function testCheckoutWithAccountCreationSendsWooCommerceNewAccountEmail() {
		// Enable guest checkout and account creation.
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );

		$product_id = $this->factory->product->createSimple( [ 'virtual' => true ] );
		WC()->cart->add_to_cart( $product_id, 1 );

		$query = '
			mutation ($input: CheckoutInput!) {
				checkout(input: $input) {
					order {
						databaseId
					}
					customer {
						databaseId
						email
					}
				}
			}
		';

		$variables = [
			'input' => [
				'paymentMethod' => 'bacs',
				'isPaid'        => true,
				'billing'       => [
					'firstName' => 'John',
					'lastName'  => 'Doe',
					'email'     => 'checkoutcustomer@example.com',
					'address1'  => '123 Test St',
					'city'      => 'Testville',
					'state'     => 'CA',
					'postcode'  => '90210',
					'country'   => 'US',
				],
				'account'       => [
					'username'     => 'checkoutcustomer',
					'password'     => 'testpassword123',
					'authenticate' => true,
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'checkout.order.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'checkout.customer.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'checkout.customer.email', 'checkoutcustomer@example.com' ),
			]
		);

		$sent = $this->find_sent_email( 'checkoutcustomer@example.com' );
		$this->assertNotNull( $sent, 'WC new account email should be sent when creating account during checkout.' );
		$this->assertStringContainsString( 'text/html', $sent->header, 'Checkout account creation email should use HTML content type from WooCommerce template.' );
	}

	public function testResetPasswordSendsWooCommerceEmail() {
		$this->factory->customer->create(
			[ 'email' => 'resetuser@example.com' ]
		);

		$query = '
			mutation ($input: SendPasswordResetEmailInput!) {
				sendPasswordResetEmail(input: $input) {
					success
				}
			}
		';

		$variables = [
			'input' => [
				'username' => 'resetuser@example.com',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'sendPasswordResetEmail.success', true ),
			]
		);

		$sent = $this->find_sent_email( 'resetuser@example.com' );
		$this->assertNotNull( $sent, 'A password reset email should be sent.' );
		$this->assertStringContainsString( 'text/html', $sent->header, 'Password reset email should use HTML content type from WooCommerce template.' );
	}
}
