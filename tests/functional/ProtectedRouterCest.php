<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;
use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;

class ProtectedRouterCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		$this->product_catalog = $I->getCatalog();

		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}
	}

	/**
	 * Helper: Starts a guest session by adding a product to the cart.
	 * Returns the cart item key and raw session token.
	 */
	private function startNewSession( FunctionalTester $I ): array {
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 5,
			]
		);

		$I->assertQuerySuccessful(
			$success,
			[ $I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ) ]
		);

		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		return [
			'key'           => $I->lodashGet( $success, 'data.addToCart.cartItem.key' ),
			'session_token' => $session_token,
		];
	}

	/**
	 * Helper: Decodes the session token and returns the customer_id (session_id).
	 */
	private function getSessionId( string $session_token ): string {
		JWT::$leeway = 60;
		$token_data  = JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		return $token_data->data->customer_id;
	}

	/**
	 * Test that a valid nonce redirects to the checkout page.
	 */
	public function testValidNonceRedirectsToCheckout( FunctionalTester $I ) {
		$session_data  = $this->startNewSession( $I );
		$session_id    = $this->getSessionId( $session_data['session_token'] );

		$query   = 'query { customer { checkoutNonce } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			[ 'woocommerce-session' => "Session {$session_data['session_token']}" ]
		);

		$checkout_nonce = $I->lodashGet( $success, 'data.customer.checkoutNonce' );
		$I->assertNotEmpty( $checkout_nonce );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_id}&_wc_checkout={$checkout_nonce}" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->seeInCurrentUrl( '/checkout' );

		$I->startFollowingRedirects();
	}

	/**
	 * Test that an invalid nonce does NOT redirect to checkout.
	 */
	public function testInvalidNonceDoesNotRedirectToCheckout( FunctionalTester $I ) {
		$session_data = $this->startNewSession( $I );
		$session_id   = $this->getSessionId( $session_data['session_token'] );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_id}&_wc_checkout=invalid_nonce" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->dontSeeInCurrentUrl( '/checkout' );

		$I->startFollowingRedirects();
	}

	/**
	 * Test that an expired nonce (after client_session_id change) does NOT redirect to checkout.
	 */
	public function testExpiredNonceDoesNotRedirectToCheckout( FunctionalTester $I ) {
		$this->startNewSession( $I );

		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		$query = '
			mutation($input: UpdateSessionInput!) {
				updateSession(input: $input) {
					session { key value }
					customer { checkoutUrl }
				}
			}
		';

		// Set client_session_id and get checkout URL.
		$success = $I->sendGraphQLRequest(
			$query,
			[
				'input' => [
					'sessionData' => [
						[ 'key' => 'client_session_id', 'value' => 'original-session-id' ],
					],
				],
			],
			[ 'woocommerce-session' => "Session {$session_token}" ]
		);

		$expired_checkout_url = $I->lodashGet( $success, 'data.updateSession.customer.checkoutUrl' );
		$I->assertNotEmpty( $expired_checkout_url );

		// Change client_session_id to invalidate the nonce.
		$I->sendGraphQLRequest(
			$query,
			[
				'input' => [
					'sessionData' => [
						[ 'key' => 'client_session_id', 'value' => 'new-session-id' ],
					],
				],
			],
			[ 'woocommerce-session' => "Session {$session_token}" ]
		);

		// The old checkout URL should no longer redirect to checkout.
		$I->stopFollowingRedirects();
		$I->amOnUrl( $expired_checkout_url );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->dontSeeInCurrentUrl( '/checkout' );
		$I->startFollowingRedirects();
	}

	/**
	 * Test that the session cart URL redirects correctly.
	 */
	public function testGetTheSessionCartUrl( FunctionalTester $I ) {
		$session_data = $this->startNewSession( $I );
		$session_id   = $this->getSessionId( $session_data['session_token'] );

		$query   = 'query { customer { cartNonce } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			[ 'woocommerce-session' => "Session {$session_data['session_token']}" ]
		);

		$cart_nonce = $I->lodashGet( $success, 'data.customer.cartNonce' );
		$I->assertNotEmpty( $cart_nonce );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_id}&_wc_cart={$cart_nonce}" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->seeInCurrentUrl( '/cart' );

		$I->startFollowingRedirects();
	}

	/**
	 * Helper: Sets up a logged-in user session and creates the my-account page.
	 * Returns auth_token, session_token, and session_id.
	 */
	private function setupAuthenticatedSession( FunctionalTester $I ): array {
		$I->setupStoreAndUsers();

		// Create the my-account page and set it as the WooCommerce account page.
		$account_page_id = $I->havePostInDatabase(
			[
				'post_type'   => 'page',
				'post_title'  => 'My Account',
				'post_name'   => 'my-account',
				'post_status' => 'publish',
			]
		);
		$I->haveOptionInDatabase( 'woocommerce_myaccount_page_id', $account_page_id );

		$login = $I->login(
			[
				'clientMutationId' => 'login',
				'username'         => 'jimbo1234@example.com',
				'password'         => 'password',
			]
		);

		$auth_token    = $I->lodashGet( $login, 'data.login.authToken' );
		$customer_id   = $I->lodashGet( $login, 'data.login.customer.databaseId' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );
		$session_id    = $this->getSessionId( $session_token );

		// For registered users, the session_id should be the user's database ID.
		$I->assertEquals( (string) $customer_id, $session_id );

		return compact( 'auth_token', 'session_token', 'session_id' );
	}

	/**
	 * Test that the session account URL redirects correctly.
	 */
	public function testGetTheSessionAccountUrl( FunctionalTester $I ) {
		$session = $this->setupAuthenticatedSession( $I );

		$query   = 'query { customer { accountNonce } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			[
				'Authorization'       => "Bearer {$session['auth_token']}",
				'woocommerce-session' => "Session {$session['session_token']}",
			]
		);

		$account_nonce = $I->lodashGet( $success, 'data.customer.accountNonce' );
		$I->assertNotEmpty( $account_nonce );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session['session_id']}&_wc_account={$account_nonce}" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->seeInCurrentUrl( '/my-account' );

		$I->startFollowingRedirects();
	}

	/**
	 * Test that the session add payment method URL redirects correctly.
	 */
	public function testGetTheSessionAddPaymentMethodUrl( FunctionalTester $I ) {
		$session = $this->setupAuthenticatedSession( $I );

		$query   = 'query { customer { addPaymentMethodNonce } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			[
				'Authorization'       => "Bearer {$session['auth_token']}",
				'woocommerce-session' => "Session {$session['session_token']}",
			]
		);

		$payment_nonce = $I->lodashGet( $success, 'data.customer.addPaymentMethodNonce' );
		$I->assertNotEmpty( $payment_nonce );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session['session_id']}&_wc_payment={$payment_nonce}" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->seeInCurrentUrl( 'add-payment-method' );

		$I->startFollowingRedirects();
	}
}
