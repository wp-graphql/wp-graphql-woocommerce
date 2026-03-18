<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;
use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;

class CustomerRegisterCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		$this->product_catalog = $I->getCatalog();

		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}
	}

	public function testRegisterCustomerAuthenticateFlag( FunctionalTester $I ) {
		/**
		 * Step 1: Create a guest session by adding an item to the cart.
		 */
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 1,
			]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ),
			]
		);

		// Grab and decode the guest session token.
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$guest_session_token = $I->grabHttpHeader( 'woocommerce-session' );
		JWT::$leeway         = 60;
		$guest_token_data    = JWT::decode( $guest_session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );
		$guest_customer_id   = $guest_token_data->data->customer_id;

		$I->assertNotEmpty( $guest_customer_id, 'Guest session should have a customer ID.' );

		/**
		 * Step 2: Register a customer WITHOUT the authenticate flag.
		 * The session token should still reference the original guest customer ID.
		 */
		$register_mutation = '
			mutation ( $input: RegisterCustomerInput! ) {
				registerCustomer( input: $input ) {
					clientMutationId
					customer {
						databaseId
					}
				}
			}
		';

		$no_auth_email     = 'noauth_' . uniqid() . '@example.com';
		$register_response = $I->sendGraphQLRequest(
			$register_mutation,
			[
				'input' => [
					'clientMutationId' => 'registerNoAuth',
					'email'            => $no_auth_email,
					'password'         => 'password123',
				],
			],
			[ 'woocommerce-session' => "Session {$guest_session_token}" ]
		);

		// Without authenticate, the user is still a guest so customer.databaseId
		// will be null (no permission). Just verify the mutation succeeded.
		$I->assertQuerySuccessful(
			$register_response,
			[
				$I->expectField( 'registerCustomer.clientMutationId', 'registerNoAuth' ),
			]
		);

		// Without authenticate, no user change occurs so the server should not
		// issue a new session token — the session remains unchanged.
		$I->dontSeeHttpHeader( 'woocommerce-session' );

		/**
		 * Step 3: Register another customer WITH the authenticate flag set to true.
		 * The session token should now reference the newly registered user's ID.
		 */
		$auth_email             = 'withauth_' . uniqid() . '@example.com';
		$register_response_auth = $I->sendGraphQLRequest(
			$register_mutation,
			[
				'input' => [
					'clientMutationId' => 'registerWithAuth',
					'email'            => $auth_email,
					'password'         => 'password123',
					'authenticate'     => true,
				],
			],
			[ 'woocommerce-session' => "Session {$guest_session_token}" ]
		);

		// With authenticate, the user is now logged in and can see their databaseId.
		$I->assertQuerySuccessful(
			$register_response_auth,
			[
				$I->expectField( 'registerCustomer.customer.databaseId', Signal::NOT_NULL ),
			]
		);

		$new_customer_id = $I->lodashGet( $register_response_auth, 'data.registerCustomer.customer.databaseId' );

		// Decode session token — should now be the authenticated user's ID.
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$auth_session_token = $I->grabHttpHeader( 'woocommerce-session' );
		$auth_token_data    = JWT::decode( $auth_session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		$I->assertNotEquals(
			$guest_customer_id,
			$auth_token_data->data->customer_id,
			'With authenticate flag, session token should NOT keep the guest customer ID.'
		);
		$I->assertEquals(
			(string) $new_customer_id,
			$auth_token_data->data->customer_id,
			'With authenticate flag, session token should reference the newly registered user.'
		);
	}
}
