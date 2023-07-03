<?php

class ProtectedRouterTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testRouteEndpoint() {
		/**
		 * Test that the default route is set to "graphql"
		 */
		$this->assertEquals( 'transfer-session', apply_filters( 'woographql_authorizing_url_endpoint', \WPGraphQL\WooCommerce\Utils\Protected_Router::$route ) );
	}

	/**
	 * Test to make sure that the rewrite rules properly include the graphql route
	 */
	public function testGraphQLRewriteRule() {
		global $wp_rewrite;
		$route = apply_filters( 'woographql_authorizing_url_endpoint', \WPGraphQL\WooCommerce\Utils\Protected_Router::$route );
		$this->assertArrayHasKey( $route . '/?$', $wp_rewrite->extra_rules_top );
	}

	public function testAddQueryVar() {
		$query_vars = [];
		$router     = \WPGraphQL\WooCommerce\Utils\Protected_Router::instance();
		$actual     = $router->add_query_var( $query_vars );
		$this->assertEquals( $actual, [ apply_filters( 'woographql_authorizing_url_endpoint', \WPGraphQL\WooCommerce\Utils\Protected_Router::$route ) ] );
	}

	public function testGetNonceNames() {
		$router = \WPGraphQL\WooCommerce\Utils\Protected_Router::instance();
		$this->assertEquals(
			[
				'cart_url'               => '_wc_cart',
				'checkout_url'           => '_wc_checkout',
				'account_url'            => '_wc_account',
				'add_payment_method_url' => '_wc_payment',
			],
			$router->get_nonce_names()
		);
	}

	public function testGetNoncePrefix() {
		$router = \WPGraphQL\WooCommerce\Utils\Protected_Router::instance();
		$this->assertEquals( 'load-cart_', $router->get_nonce_prefix( 'cart_url' ) );
		$this->assertEquals( 'load-checkout_', $router->get_nonce_prefix( 'checkout_url' ) );
		$this->assertEquals( 'load-account_', $router->get_nonce_prefix( 'account_url' ) );
		$this->assertEquals( 'add-payment-method_', $router->get_nonce_prefix( 'add_payment_method_url' ) );
		$this->assertEquals( null, $router->get_nonce_prefix( 'invalid' ) );
	}

	public function testGetTargetEndpoint() {
		$router = \WPGraphQL\WooCommerce\Utils\Protected_Router::instance();
		$this->assertEquals( get_permalink( wc_get_page_id( 'cart' ) ), $router->get_target_endpoint( 'cart_url' ) );
		$this->assertEquals( wc_get_endpoint_url( 'checkout' ), $router->get_target_endpoint( 'checkout_url' ) );
		$this->assertEquals( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ), $router->get_target_endpoint( 'account_url' ) );
		$this->assertEquals( wc_get_account_endpoint_url( 'add-payment-method' ), $router->get_target_endpoint( 'add_payment_method_url' ) );
		$this->assertEquals( null, $router->get_nonce_prefix( 'invalid' ) );
	}
}
