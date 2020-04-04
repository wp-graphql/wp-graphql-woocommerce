<?php
/**
 * Disable autoloading while running tests, as the test
 * suite already bootstraps the autoloader and creates
 * fatal errors when the autoloader is loaded twice
 */
define( 'GRAPHQL_DEBUG', true );
define( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD', getenv( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) );
define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', 'testingtesting123' );
<<<<<<< HEAD
=======
define( 'STRIPE_API_PUBLISHABLE_KEY', getenv( 'STRIPE_API_PUBLISHABLE_KEY' ) );
define( 'STRIPE_API_SECRET_KEY', getenv( 'STRIPE_API_SECRET_KEY' ) );
>>>>>>> Stripe payment gateway support implemented
