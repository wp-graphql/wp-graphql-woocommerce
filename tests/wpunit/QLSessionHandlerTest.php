<?php
/**
 * Unit test for QL_Session_Handler
 */

use WPGraphQL\WooCommerce\Utils\QL_Session_Handler;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;

if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
	define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'graphql-woo-cart-session' );
}

class QLSessionHandlerTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	
	public function setUp(): void {
		parent::setUp();

		$this->markTestSkipped( 'Skipping test for now.' );

		// before
		unset( $_SERVER['HTTP_WOOCOMMERCE_SESSION'] );
		$customer_cookie_key = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
		wc_setcookie( $customer_cookie_key, 0, time() - HOUR_IN_SECONDS );
		unset( $_COOKIE[ $customer_cookie_key ] );
	}
	public function tearDown(): void {
		unset( $_SERVER );
		WC()->session->destroy_session();

		// after
		parent::tearDown();
	}

	// Tests
	public function test_initializes() {
		// Create session handler.
		$session = new QL_Session_Handler();

		$this->assertInstanceOf( QL_Session_Handler::class, $session );
	}

	public function test_init_on_graphql_request() {
		// Simulate GraphQL HTTP Request.
		add_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Create session handler.
		$session = new QL_Session_Handler();

		// Assert session hasn't started.
		$this->assertFalse( $session->has_session(), 'Shouldn\'t have a session yet' );

		// Initialize session.
		$session->init();

		// Assert session has started.
		$this->assertTrue( $session->has_session(), 'Should have session.' );

		// Get token for future request.
		$old_token         = $session->build_token();
		$decoded_old_token = JWT::decode( $old_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		// Sent token to HTTP header to simulate a new request.
		$_SERVER['HTTP_WOOCOMMERCE_SESSION'] = 'Session ' . $old_token;

		// Stale for 5 seconds so timers can update.
		usleep( 1000000 );

		// Initialize session token for next request.
		remove_action( 'woocommerce_set_cart_cookies', [ $session, 'set_customer_session_token' ] );
		remove_action( 'woographql_update_session', [ $session, 'set_customer_session_token' ] );
		remove_action( 'shutdown', [ $session, 'save_data' ] );

		// Create new session handler.
		$session = new QL_Session_Handler();
		$session->init();
		$new_token         = $session->build_token();
		$decoded_new_token = JWT::decode( $new_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );

		// Assert new token is different than old token.
		$this->assertNotEquals( $old_token, $new_token, 'New token should not match token from last request.' );
		$this->assertGreaterThan( $decoded_old_token->exp, $decoded_new_token->exp );

		// Assert customer ID match
		$this->assertEquals( $decoded_old_token->data->customer_id, $decoded_new_token->data->customer_id );
	}

	public function test_init_on_non_graphql_request() {
		// Create session handler.
		$session = new QL_Session_Handler();

		// Assert session hasn't started.
		$this->assertFalse( $session->has_session(), 'Shouldn\'t have a session yet' );

		// Initialize session.
		$session->init();

		// Add product to cart and start the session.
		$this->factory->cart->add(
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
		);

		// Assert no tokens are being issued.
		$this->assertFalse( $session->sending_token(), 'Should not be issuing a new customer token.' );
		$this->assertFalse( $session->build_token(), 'Should not be issuing a new customer token.' );
	}

	public function test_init_on_non_graphql_request_with_session_token() {
		// Simulate GraphQL HTTP Request.
		add_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Create session handler.
		$session = new QL_Session_Handler();

		// Assert session hasn't started.
		$this->assertFalse( $session->has_session(), 'Shouldn\'t have a session yet' );

		// Initialize session.
		$session->init();

		// Assert session has started.
		$this->assertTrue( $session->has_session(), 'Should have session.' );

		// Get token for future request.
		$token_to_session = $session->build_token();

		// Remove GraphQL HTTP Request filter to simulate normal request.
		remove_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Sent token to HTTP header to simulate a new request.
		$_SERVER['HTTP_WOOCOMMERCE_SESSION'] = 'Session ' . $token_to_session;

		// Create session handler.
		$session = new QL_Session_Handler();

		// Assert session hasn't started.
		$this->assertFalse( $session->has_session(), 'Shouldn\'t have a session yet' );

		// Initialize session.
		$session->init();

		// Assert session has started.
		$this->assertTrue( $session->has_session(), 'Should have session.' );

		// Assert tokens are being issued.
		$this->assertNotFalse( $session->build_token() );
	}

	public function test_get_session_token() {
		// Simulate GraphQL HTTP Request.
		add_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Create session handler.
		$session = new QL_Session_Handler();

		// Expect token to be null.
		$null_token = $session->get_session_token();
		$this->assertFalse( $null_token, 'No token should exist.' );

		// Set token in header.
		$session->init_session_token();
		$_SERVER['HTTP_WOOCOMMERCE_SESSION'] = 'Session ' . $session->build_token();

		// Expect token to be value.
		$token = $session->get_session_token();
		$this->assertTrue( ! empty( $token->iat ) );
		$this->assertTrue( ! empty( $token->exp ) );
		$this->assertTrue( ! empty( $token->data ) );
	}

	public function test_get_session_header() {
		// Create session handler.
		$session = new QL_Session_Handler();
		$session->init_session_token();

		// Get the Auth header.
		$null_header = $session->get_session_header();

		$this->assertFalse( $null_header, 'No HTTP Header with session token should exist.' );

		// Set token in header.
		$_SERVER['HTTP_WOOCOMMERCE_SESSION'] = 'Session ' . $session->build_token();

		$this->assertIsString( $session->get_session_header() );
	}

	public function test_build_token() {
		// Simulate GraphQL HTTP Request.
		add_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Create session handler.
		$session = new QL_Session_Handler();

		// Should be invalid if run before initialization.
		$invalid_token = $session->build_token();
		$this->assertFalse( $invalid_token, 'Should be an invalid session token' );

		// Should valid when run after initialization.
		$session->init_session_token();
		$token = $session->build_token();

		$decode_token = JWT::decode( $token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );
		$this->assertTrue( ! empty( $decode_token->iat ) );
		$this->assertTrue( ! empty( $decode_token->exp ) );
		$this->assertTrue( ! empty( $decode_token->data ) );

		$this->assertEquals( $token, $session->build_token() );
	}

	public function test_set_customer_session_token() {
		// Simulate GraphQL HTTP Request.
		add_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Create session handler.
		$session = new QL_Session_Handler();

		// Should fail to set headers if run before initialization.
		$session->set_customer_session_token( true );
		$graphql_response_headers = apply_filters( 'graphql_response_headers_to_send', [] );
		$this->assertArrayNotHasKey( 'woocommerce-session', $graphql_response_headers );

		// Should success when run after initialization.
		$session->init_session_token();
		$graphql_response_headers = apply_filters( 'graphql_response_headers_to_send', [] );
		$this->assertArrayHasKey( 'woocommerce-session', $graphql_response_headers );
	}

	public function test_forget_session() {
		// Simulate GraphQL HTTP Request.
		add_filter( 'graphql_is_graphql_http_request', '__return_true' );

		// Create session handler.
		$session = new QL_Session_Handler();
		$session->init_session_token();

		// Get old token
		$old_token = $session->build_token();
		$this->assertIsString( $old_token );

		// Forget session
		$session->forget_session();

		// Get new token.
		$new_token = $session->build_token();
		$this->assertIsString( $old_token );

		$this->assertNotEquals( $old_token, $new_token, 'Tokens should not match' );
	}

	public function test_get_client_session_id() {
		// Create session handler.
		$session = new QL_Session_Handler();

		// Assert an random string returned, when valid "client_session_id" and "client_session_id_expiration" are not set.
		$session->init_session_cookie();
		$this->assertNotEquals( '', $session->get_client_session_id() );

		$session->init_session_cookie();
		$_REQUEST['_wc_cart'] = 'test';
		$this->assertNotEquals( '', $session->get_client_session_id() );

		$session->init_session_cookie();
		$session->set( 'client_session_id', 'test-client-session-id' );
		$session->set( 'client_session_id_expiration', '1' );
		$this->assertNotEquals( 'test-client-session-id', $session->get_client_session_id() );

		// Assert "test-client-session-id" is returned, when valid "client_session_id" and "client_session_id_expiration" are set.
		$session->init_session_cookie();
		$session->set( 'client_session_id', 'test-client-session-id' );
		$session->set( 'client_session_id_expiration', ( time() + 3600 ) );
		$this->assertEquals( 'test-client-session-id', $session->get_client_session_id() );
	}
}
