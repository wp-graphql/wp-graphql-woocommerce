<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;
use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;

/**
 * Tests session transfer behavior when a user logs in with an existing session.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/909
 */
class SessionTransferCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		$this->product_catalog = $I->getCatalog();

		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}
	}

	/**
	 * Helper: Sets up a scenario with an existing user session and a new guest session.
	 *
	 * 1. Logs in, adds product A to cart, saves session (simulates previous device)
	 * 2. Adds product B to cart as guest (simulates new device)
	 * 3. Logs in again with the guest session — triggers session transfer
	 * 4. Returns tokens for querying the resulting cart
	 *
	 * @param FunctionalTester $I       Tester instance.
	 * @param string           $setting The session_transfer_behavior setting value.
	 *
	 * @return array{ auth_token: string, session_token: string }
	 */
	private function setupSessionTransferScenario( FunctionalTester $I, string $setting ): array {
		$I->setupStoreAndUsers();

		// Set the session transfer behavior setting.
		$existing = $I->grabOptionFromDatabase( 'woographql_settings' );
		$I->haveOptionInDatabase(
			'woographql_settings',
			array_merge(
				is_array( $existing ) ? $existing : [],
				[ 'session_transfer_behavior' => $setting ]
			)
		);

		/**
		 * Step 1: Add t-shirt as guest, then log in with that session.
		 */
		$add_old = $I->addToCart(
			[
				'clientMutationId' => 'addOld',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 1,
			]
		);

		$I->assertQuerySuccessful(
			$add_old,
			[ $I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ) ]
		);

		$old_session = $I->grabHttpHeader( 'woocommerce-session' );

		// Log in with the guest session — transfers t-shirt cart to user.
		$login_1 = $I->login(
			[
				'clientMutationId' => 'login1',
				'username'         => 'jimbo1234@example.com',
				'password'         => 'password',
			],
			[
				'woocommerce-session' => "Session {$old_session}",
			]
		);

		$I->assertQuerySuccessful(
			$login_1,
			[ $I->expectField( 'login.authToken', Signal::NOT_NULL ) ]
		);

		/**
		 * Step 2: Add jeans as a new guest (no session headers — fresh session).
		 */
		$guest_add = $I->addToCart(
			[
				'clientMutationId' => 'addNew',
				'productId'        => $this->product_catalog['jeans'],
				'quantity'         => 2,
			]
		);

		$I->assertQuerySuccessful(
			$guest_add,
			[ $I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ) ]
		);

		$guest_session = $I->grabHttpHeader( 'woocommerce-session' );

		/**
		 * Step 3: Log in again with the guest session — triggers session transfer.
		 */
		$login_2 = $I->login(
			[
				'clientMutationId' => 'login2',
				'username'         => 'jimbo1234@example.com',
				'password'         => 'password',
			],
			[
				'woocommerce-session' => "Session {$guest_session}",
			]
		);

		$I->assertQuerySuccessful(
			$login_2,
			[ $I->expectField( 'login.authToken', Signal::NOT_NULL ) ]
		);

		return [
			'auth_token'    => $I->lodashGet( $login_2, 'data.login.authToken' ),
			'session_token' => $I->grabHttpHeader( 'woocommerce-session' ),
		];
	}

	/**
	 * Helper: Queries the cart and returns product database IDs.
	 */
	private function getCartProductIds( FunctionalTester $I, string $auth_token, string $session_token ): array {
		$response = $I->sendGraphQLRequest(
			'query { cart { contents { nodes { product { node { databaseId } } } } } }',
			[],
			[
				'Authorization'       => "Bearer {$auth_token}",
				'woocommerce-session' => "Session {$session_token}",
			]
		);

		$nodes = $I->lodashGet( $response, 'data.cart.contents.nodes', [] );

		return array_map(
			static function ( $item ) {
				return $item['product']['node']['databaseId'];
			},
			$nodes
		);
	}

	/**
	 * Test 'keep_new' — only the current guest session data is kept, old session discarded.
	 */
	public function testKeepNewSessionTransfer( FunctionalTester $I ) {
		$tokens      = $this->setupSessionTransferScenario( $I, 'keep_new' );
		$product_ids = $this->getCartProductIds( $I, $tokens['auth_token'], $tokens['session_token'] );

		$I->assertContains(
			$this->product_catalog['jeans'],
			$product_ids,
			'Jeans from guest session should be in cart.'
		);
		$I->assertNotContains(
			$this->product_catalog['t-shirt'],
			$product_ids,
			'T-shirt from previous session should NOT be in cart with keep_new.'
		);
	}

	/**
	 * Test 'keep_old' — the previously saved user session data is restored.
	 */
	public function testKeepOldSessionTransfer( FunctionalTester $I ) {
		$tokens      = $this->setupSessionTransferScenario( $I, 'keep_old' );
		$product_ids = $this->getCartProductIds( $I, $tokens['auth_token'], $tokens['session_token'] );

		$I->assertContains(
			$this->product_catalog['t-shirt'],
			$product_ids,
			'T-shirt from previous session should be in cart with keep_old.'
		);
		$I->assertNotContains(
			$this->product_catalog['jeans'],
			$product_ids,
			'Jeans from guest session should NOT be in cart with keep_old.'
		);
	}

	/**
	 * Test 'keep_new_fallback_old' (default) — keeps new if non-empty, falls back to old.
	 */
	public function testKeepNewFallbackOldSessionTransfer( FunctionalTester $I ) {
		$tokens      = $this->setupSessionTransferScenario( $I, 'keep_new_fallback_old' );
		$product_ids = $this->getCartProductIds( $I, $tokens['auth_token'], $tokens['session_token'] );

		// Guest session had items (hoodie), so new data is kept.
		$I->assertContains(
			$this->product_catalog['jeans'],
			$product_ids,
			'Jeans from guest session should be in cart (new data non-empty).'
		);
		$I->assertNotContains(
			$this->product_catalog['t-shirt'],
			$product_ids,
			'T-shirt from previous session should NOT be in cart when new data is non-empty.'
		);
	}
}
