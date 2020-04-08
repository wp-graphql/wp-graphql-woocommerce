<?php
/**
 * Disable autoloading while running tests, as the test
 * suite already bootstraps the autoloader and creates
 * fatal errors when the autoloader is loaded twice
 */
if ( ! defined( 'GRAPHQL_DEBUG' ) ) {
    define( 'GRAPHQL_DEBUG', true );
}

if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) ) {
    define( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD', getenv( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) );
}

if ( ! defined( 'GRAPHQL_JWT_AUTH_SECRET_KEY' ) ) {
    define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', getenv( 'GRAPHQL_JWT_AUTH_SECRET_KEY' ) );
}

if ( ! defined( 'STRIPE_API_PUBLISHABLE_KEY' ) ) {
    define( 'STRIPE_API_PUBLISHABLE_KEY', getenv( 'STRIPE_API_PUBLISHABLE_KEY' ) );
}

if ( ! defined( 'STRIPE_API_SECRET_KEY' ) ) {
    define( 'STRIPE_API_SECRET_KEY', getenv( 'STRIPE_API_SECRET_KEY' ) );
}
