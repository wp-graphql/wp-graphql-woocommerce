<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;
use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;

/**
 * Tests that the session token is correctly linked to the authenticated
 * user after login, even when hCaptcha for WP is active.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/941
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/942
 */
class HCaptchaSessionCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		$this->product_catalog = $I->getCatalog();

		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}

		// Activate and configure hCaptcha plugin for this test.
		$I->cli( [ 'plugin', 'activate', 'hcaptcha-for-forms-and-more' ] );
		$I->haveOptionInDatabase(
			'hcaptcha_settings',
			[
				'site_key'   => '10000000-ffff-ffff-ffff-000000000001',
				'secret_key' => '0x0000000000000000000000000000000000000000',
				'wp_status'  => [ 'login' ],
			]
		);
	}

	public function _after( FunctionalTester $I ) {
		$I->cli( [ 'plugin', 'deactivate', 'hcaptcha-for-forms-and-more' ] );
	}

	public function testLoginSessionTokenLinkedToAuthenticatedUser( FunctionalTester $I ) {
		$I->setupStoreAndUsers();

		/**
		 * Step 1: Add item to cart as a guest to establish a session.
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
		 * Step 2: Log in with the login mutation.
		 */
		$login_input = [
			'clientMutationId' => 'loginId',
			'username'         => 'jimbo1234@example.com',
			'password'         => 'password',
		];

		$login_response = $I->login( $login_input );

		$I->assertQuerySuccessful(
			$login_response,
			[
				$I->expectField( 'login.customer.databaseId', Signal::NOT_NULL ),
				$I->expectField( 'login.authToken', Signal::NOT_NULL ),
				$I->expectField( 'login.sessionToken', Signal::NOT_NULL ),
			]
		);

		$auth_token    = $I->lodashGet( $login_response, 'data.login.authToken' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );
		$customer_id   = $I->lodashGet( $login_response, 'data.login.customer.databaseId' );

		// Decode the session token from login — should be the authenticated user, not guest.
		$login_token_data = JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		$I->assertNotEquals(
			$guest_customer_id,
			$login_token_data->data->customer_id,
			'Session token after login should NOT be the guest customer ID.'
		);
		$I->assertEquals(
			(string) $customer_id,
			$login_token_data->data->customer_id,
			'Session token after login should be the authenticated user ID.'
		);

		// Also decode the session token from the login response to verify it matches the header token and has the correct user ID.
		$session_token_data = JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		$I->assertEquals(
			(string) $customer_id,
			$session_token_data->data->customer_id,
			'Session token after login should be the authenticated user ID, not a new guest.'
		);

		/**
		 * Step 3: Send an updateSession mutation with authToken and sessionToken.
		 */
		$update_session_mutation = '
			mutation($input: UpdateSessionInput!) {
				updateSession(input: $input) {
					customer {
						databaseId
						sessionToken
					}
				}
			}
		';

		$update_response = $I->sendGraphQLRequest(
			$update_session_mutation,
			[
				'input' => [
					'sessionData' => [
						[
							'key'   => 'test_key',
							'value' => 'test_value',
						],
					],
				],
			],
			[
				'Authorization'       => "Bearer {$auth_token}",
				'woocommerce-session' => "Session {$session_token}",
			]
		);

		$I->assertQuerySuccessful(
			$update_response,
			[
				$I->expectField( 'updateSession.customer.databaseId', $customer_id ),
			]
		);

		// Decode the session token from updateSession response.
		$updated_session_token = $I->lodashGet( $update_response, 'data.updateSession.customer.sessionToken' );
		$I->assertNotEmpty( $updated_session_token, 'updateSession should return a session token.' );

		$updated_token_data = JWT::decode( $updated_session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		$I->assertEquals(
			(string) $customer_id,
			$updated_token_data->data->customer_id,
			'Session token after updateSession should still be the authenticated user ID, not a new guest.'
		);
	}

	/**
	 * Reproduces the exact scenario from #941: a cold login (no prior session)
	 * should return a session token linked to the authenticated user, not a guest.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/941
	 */
	public function testColdLoginReturnsAuthenticatedSessionToken( FunctionalTester $I ) {
		$I->setupStoreAndUsers();

		$login_response = $I->login(
			[
				'clientMutationId' => 'coldLoginId',
				'username'         => 'jimbo1234@example.com',
				'password'         => 'password',
			]
		);

		$I->assertQuerySuccessful(
			$login_response,
			[
				$I->expectField( 'login.customer.databaseId', Signal::NOT_NULL ),
				$I->expectField( 'login.authToken', Signal::NOT_NULL ),
			]
		);

		$customer_id   = $I->lodashGet( $login_response, 'data.login.customer.databaseId' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		// Decode the session token from the response header and verify the customer ID matches.
		JWT::$leeway  = 60;
		$token_data   = JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		$I->assertEquals(
			(string) $customer_id,
			$token_data->data->customer_id,
			'Session token after login should contain the authenticated user ID, not a guest.'
		);
	}
}
