<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;

/**
 * Tests that the Protected Router's transfer-session endpoint works
 * when Elementor is active with an "entire website" template.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/945
 */
class ElementorCompatibilityCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}

		// Enable authorizing URL fields for nonce handling.
		$I->setWooGraphQLSetting(
			'enable_authorizing_url_fields',
			[
				'cart_url'               => 'cart_url',
				'checkout_url'           => 'checkout_url',
				'account_url'            => 'account_url',
				'add_payment_method_url' => 'add_payment_method_url',
			]
		);

		$this->product_catalog = $I->getCatalog();

		// Activate Elementor.
		activate_plugin( 'elementor/elementor.php' );

		// Create an Elementor template with "entire website" condition.
		$template_id = $I->havePostInDatabase(
			[
				'post_type'   => 'elementor_library',
				'post_title'  => 'Entire Site Template',
				'post_status' => 'publish',
				'meta_input'  => [
					'_elementor_template_type' => 'page',
					'_elementor_conditions'    => [ 'include/general' ],
					'_elementor_data'          => '[]',
					'_elementor_edit_mode'     => 'builder',
				],
			]
		);

		// Set Elementor's active kit and conditions cache.
		$I->haveOptionInDatabase( 'elementor_active_kit', $template_id );
	}

	public function _after( FunctionalTester $I ) {
		deactivate_plugins( 'elementor/elementor.php' );
	}

	/**
	 * Helper: Starts a guest session by adding a product to the cart.
	 */
	private function startNewSession( FunctionalTester $I ): array {
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 1,
			]
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
	 * Test that the transfer-session endpoint still works when Elementor is
	 * active with an "entire website" template applied.
	 */
	public function testTransferSessionWorksWithElementorActive( FunctionalTester $I ) {
		$session_data = $this->startNewSession( $I );
		$session_id   = $this->getSessionId( $session_data['session_token'] );

		$query   = 'query { customer { checkoutNonce } }';
		$success = $I->sendGraphQLRequest(
			$query,
			null,
			[ 'woocommerce-session' => "Session {$session_data['session_token']}" ]
		);

		$checkout_nonce = $I->lodashGet( $success, 'data.customer.checkoutNonce' );
		$I->assertNotEmpty( $checkout_nonce, 'checkoutNonce should be returned.' );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_id}&_wc_checkout={$checkout_nonce}" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->seeInCurrentUrl( '/checkout' );

		$I->startFollowingRedirects();
	}

	/**
	 * Test that the transfer-session endpoint does not return a 500 error
	 * when Elementor is active, even with an invalid nonce.
	 */
	public function testTransferSessionDoesNotReturn500WithElementor( FunctionalTester $I ) {
		$session_data = $this->startNewSession( $I );
		$session_id   = $this->getSessionId( $session_data['session_token'] );

		$I->stopFollowingRedirects();

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$session_id}&_wc_checkout=invalid_nonce" );

		// Should redirect to home (302), NOT 500.
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->dontSeeInCurrentUrl( '/checkout' );

		$I->startFollowingRedirects();
	}
}
