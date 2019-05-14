<?php
/**
 * Plugin Name: WP GraphQL WooCommerce
 * Plugin URI: https://github.com/kidunot89/wp-graphql-woocommerce
 * Description: Adds Woocommerce types to WPGraphQL schema.
 * Version: 0.1.0
 * Author: kidunot89
 * Author URI: https://axistaylor.com
 * Text Domain: wp-graphql-woocommerce
 * Domain Path: /languages
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WPGraphQL\Extensions\WooCommerce
 * @author      kidunot89
 * @license     GPL-3
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

defined( 'GRAPHQL_DEBUG' ) || define( 'GRAPHQL_DEBUG', true );

/**
 * If the codeception remote coverage file exists, require it.
 *
 * This file should only exist locally or when CI bootstraps the environment for testing
 */
if ( file_exists( __DIR__ . '/c3.php' ) ) {
	require_once __DIR__ . '/c3.php';
}

/**
 * Setups WPGraphQL WooCommerce constants
 */
function wp_graphql_woocommerce_constants() {
	// Plugin version.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_VERSION' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_VERSION', '0.0.1' );
	}
	// Plugin Folder Path.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}
	// Plugin Folder URL.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_PLUGIN_URL' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}
	// Plugin Root File.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_PLUGIN_FILE' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_PLUGIN_FILE', __FILE__ );
	}
	// Whether to autoload the files or not.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD', true );
	}
}

/**
 * Checks if WPGraphQL WooCommerce required plugins are installed and activated
 */
function wp_graphql_woocommerce_dependencies_not_ready() {
	$deps = [];
	if ( ! class_exists( '\WPGraphQL' ) ) {
		$deps[] = 'WPGraphQL';
	}
	if ( ! class_exists( '\WooCommerce' ) ) {
		$deps[] = 'WooCommerce';
	}

	return $deps;
}

/**
 * Initializes WPGraphQL WooCommerce
 */
function wp_graphql_woocommerce_init() {
	wp_graphql_woocommerce_constants();

	$not_ready = wp_graphql_woocommerce_dependencies_not_ready();
	if ( empty( $not_ready ) ) {
		require_once WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-wp-graphql-woocommerce.php';
		return WP_GraphQL_WooCommerce::instance();
	}

	foreach ( $not_ready as $dep ) {
		add_action(
			'admin_notices',
			function() use ( $dep ) {
				?>
				<div class="error notice">
					<p>
						<?php
							printf(
								/* translators: dependency not ready error message */
								esc_html__( '%1$s must be active for wp-graphql-woocommerce to work', 'wp-graphql-woocommerce' ),
								esc_html( $dep )
							);
						?>
					</p>
				</div>
				<?php
			}
		);
	}

	return false;
}
add_action( 'graphql_init', 'wp_graphql_woocommerce_init' );
