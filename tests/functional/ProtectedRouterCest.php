<?php
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;

class ProtectedRouterCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		// Create Products
		$this->product_catalog = $I->getCatalog();

		// Flush permalinks.
		$I->loginAsAdmin();
		$I->amOnAdminPage( 'options-permalink.php' );
		$I->click( '#submit' );
		$I->logOut();

		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestest' );
		}
	}

	public function _startNewSession( FunctionalTester $I ) {
		$I->wantTo( 'Start new session by adding an item to the cart' );
		/**
		 * Add t-shirt to the cart
		 */
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 5,
			],
		);

		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey( 'data', $success );
		$I->assertArrayHasKey( 'addToCart', $success['data'] );
		$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
		$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );
		$key = $success['data']['addToCart']['cartItem']['key'];

		/**
		 * Assert existence and validity of "woocommerce-session" HTTP header.
		 */
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		return compact( 'key', 'session_token' );
	}

	public function _getLastRequestHeaders( $I ) {
		$headers = [
			'woocommerce-session' => 'Session ' . $I->wantHTTPResponseHeaders( 'woocommerce-session' ),
		];

		return $headers;
	}

	public function tryToProceedToCheckoutPage( FunctionalTester $I ) {
		$session_data  = $this->_startNewSession( $I );
		$session_token = $session_data['session_token'];
		// Retrieve and decode token for session_id.
		JWT::$leeway   = 60;
		$token_data    = ! empty( $session_token )
			? JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) )
			: null;
		$session_token = $token_data->data->customer_id;

		$I->wantTo( 'Get the session checkout URL' );
		$query   = 'query { customer { checkoutNonce } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			$this->_getLastRequestHeaders( $I )
		);

		// Assert "checkoutUrl" was received.
		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey( 'data', $success );
		$I->assertArrayHasKey( 'customer', $success['data'] );
		$I->assertArrayHasKey( 'checkoutNonce', $success['data']['customer'] );
		$checkout_nonce = $success['data']['customer']['checkoutNonce'];

		$I->wantTo( 'Go checkout page and confirm session not seen' );
		$I->amOnPage( '/checkout' );
		$I->seeElement('.wc-empty-cart-message');

		$I->wantTo( 'Authenticate with nonced url and confirm page redirect to checkout page' );
		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );

		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_token}&_wc_checkout={$checkout_nonce}" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->seeInCurrentUrl( '/checkout/' );
		$I->startFollowingRedirects();

		$I->wantTo( 'Confirm session has been loaded.' );
		$I->see( 'Checkout' );
		$I->see( 't-shirt' );
	}


	public function tryToProceedToCheckoutPageWithExpiredUrl( FunctionalTester $I ) {
		$this->_startNewSession( $I );

		$I->wantTo( 'Get the session checkout URL' );
		$query   = '
            mutation($input: UpdateSessionInput!) {
                updateSession(input: $input) {
                    session {
                        id
                        key
                        value
                    }
                    customer { checkoutUrl }
                }
            }
        ';
		$success = $I->sendGraphQLRequest(
			$query,
			[
				'sessionData' => [
					[
						'key'   => 'client_session_id',
						'value' => 'test-client-session-id',
					],
				],
			],
			$this->_getLastRequestHeaders( $I )
		);

		// Assert updateSession was success.
		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey( 'data', $success );
		$I->assertArrayHasKey( 'updateSession', $success['data'] );
		$I->assertArrayHasKey( 'session', $success['data']['updateSession'] );
		$session = $success['data']['updateSession']['session'];
		$session = array_column( $session, 'value', 'key' );
		$I->assertEquals( $session['client_session_id'], 'test-client-session-id' );

		// Assert "checkoutUrl" was received.
		$I->assertArrayHasKey( 'customer', $success['data']['updateSession'] );
		$I->assertArrayHasKey( 'checkoutUrl', $success['data']['updateSession']['customer'] );
		$expired_checkout_url = $success['data']['updateSession']['customer']['checkoutUrl'];

		$I->wantTo( 'Invalidate Checkout URL by updating the "client_session_id"' );
		$success = $I->sendGraphQLRequest(
			$query,
			[
				'sessionData' => [
					[
						'key'   => 'client_session_id',
						'value' => 'new-test-client-session-id',
					],
				],
			],
			$this->_getLastRequestHeaders( $I )
		);

		// Assert updateSession was success.
		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey( 'data', $success );
		$I->assertArrayHasKey( 'updateSession', $success['data'] );
		$I->assertArrayHasKey( 'session', $success['data']['updateSession'] );
		$session = $success['data']['updateSession']['session'];
		$session = array_column( $session, 'value', 'key' );
		$I->assertEquals( $session['client_session_id'], 'new-test-client-session-id' );

		$I->wantTo( 'Go checkout page and confirm session not seen' );
		$I->amOnPage( '/checkout' );
		$I->seeElement('.wc-empty-cart-message');

		$I->wantTo( 'Attempt to authenticate with expired url and confirm page redirect to checkout page' );
		$I->stopFollowingRedirects();
		$I->amOnUrl( $expired_checkout_url );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->dontSeeInCurrentUrl( '/checkout/' );
		$I->startFollowingRedirects();
	}


	public function tryToProceedToCheckoutPageWithInvalidNonce( FunctionalTester $I ) {
		$session_data  = $this->_startNewSession( $I );
		$session_token = $session_data['session_token'];
		// Retrieve and decode token for session_id.
		JWT::$leeway   = 60;
		$token_data    = ! empty( $session_token )
			? JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) )
			: null;
		$session_token = $token_data->data->customer_id;

		$I->wantTo( 'Get the session checkout URL' );
		$query   = 'query { customer { checkoutUrl } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			$this->_getLastRequestHeaders( $I )
		);

		// Assert "checkoutUrl" was received.
		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey( 'data', $success );
		$I->assertArrayHasKey( 'customer', $success['data'] );
		$I->assertArrayHasKey( 'checkoutUrl', $success['data']['customer'] );

		$I->wantTo( 'Go checkout page and confirm session not seen' );
		$I->amOnPage( '/checkout' );
		$I->seeElement('.wc-empty-cart-message');

		$I->wantTo( 'Attempt to authenticate with nonced url and confirm page redirect to checkout page' );
		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );

		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_token}&_wc_checkout=12345" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->dontSeeInCurrentUrl( '/checkout/' );
		$I->startFollowingRedirects();
	}
}
