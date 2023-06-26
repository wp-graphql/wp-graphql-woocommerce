<?php
/**
 * Disable autoloading while running tests, as the test
 * suite already bootstraps the autoloader and creates
 * fatal errors when the autoloader is loaded twice
 */
if ( ! defined( 'GRAPHQL_DEBUG' ) ) {
	define( 'GRAPHQL_DEBUG', true );
}

if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) && false !== getenv( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) ) {
	define( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD', true );
}

if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' ) ) {
	define( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS', true );
}
if ( ! defined( 'CART_URL_NONCE_PARAM' ) ) {
	define( 'CART_URL_NONCE_PARAM', '_wc_cart' );
}
if ( ! defined( 'CHECKOUT_URL_NONCE_PARAM' ) ) {
	define( 'CHECKOUT_URL_NONCE_PARAM', '_wc_checkout' );
}
if ( ! defined( 'ACCOUNT_URL_NONCE_PARAM' ) ) {
	define( 'ACCOUNT_URL_NONCE_PARAM', '_wc_account' );
}
if ( ! defined( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) ) {
	define( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM', '_wc_payment' );
}

if ( ! defined( 'GRAPHQL_JWT_AUTH_SECRET_KEY' ) ) {
	define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', 'testingtesting123' );
}

if ( ! defined( 'HPOS' ) && ! empty( getenv( 'HPOS' ) ) ) {
	define( 'HPOS', true );
}

if ( ! defined( 'STRIPE_API_PUBLISHABLE_KEY' ) && false !== getenv( 'STRIPE_API_PUBLISHABLE_KEY' ) ) {
	define( 'STRIPE_API_PUBLISHABLE_KEY', getenv( 'STRIPE_API_PUBLISHABLE_KEY' ) );
}

if ( ! defined( 'STRIPE_API_SECRET_KEY' ) && false !== getenv( 'STRIPE_API_SECRET_KEY' ) ) {
	define( 'STRIPE_API_SECRET_KEY', getenv( 'STRIPE_API_SECRET_KEY' ) );
}


