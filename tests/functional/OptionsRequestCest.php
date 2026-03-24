<?php

/**
 * Tests that CORS preflight OPTIONS requests to the GraphQL endpoint
 * do not create WooCommerce sessions.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/908
 */
class OptionsRequestCest {
	public function _before( FunctionalTester $I ) {
		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}
	}

	/**
	 * Test that an OPTIONS request to /graphql does not return a woocommerce-session header.
	 */
	public function testOptionsRequestDoesNotCreateSession( FunctionalTester $I ) {
		$I->sendOptions( '/graphql' );
		$I->seeResponseCodeIs( 200 );

		// OPTIONS response should NOT contain a woocommerce-session header.
		$I->dontSeeHttpHeader( 'woocommerce-session' );
	}
}
