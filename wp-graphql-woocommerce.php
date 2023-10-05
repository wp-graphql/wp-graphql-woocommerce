<?php
/**
 * Plugin Name: WPGraphQL WooCommerce (WooGraphQL)
 * Plugin URI: https://github.com/wp-graphql/wp-graphql-woocommerce
 * Description: Adds Woocommerce Functionality to WPGraphQL schema.
 * Version: 0.18.1
 * Author: kidunot89
 * Author URI: https://axistaylor.com
 * Text Domain: wp-graphql-woocommerce
 * Domain Path: /languages
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 7.9.0
 * WC tested up to: 8.1.1
 * WPGraphQL requires at least: 1.16.0+
 * WPGraphQL-JWT-Authentication requires at least: 0.7.0+
 *
 * @package     WPGraphQL\WooCommerce
 * @author      kidunot89
 * @license     GPL-3
 */

namespace WPGraphQL\WooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setups WPGraphQL WooCommerce constants
 *
 * @return void
 */
function constants() {
	// Plugin version.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_VERSION' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_VERSION', '0.18.1' );
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
 * Returns path to plugin root directory.
 *
 * @return string
 */
function get_plugin_directory() {
	return trailingslashit( WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR );
}

/**
 * Returns path to plugin "includes" directory.
 *
 * @return string
 */
function get_includes_directory() {
	return trailingslashit( WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR ) . 'includes/';
}

/**
 * Returns path to plugin "vendor" directory.
 *
 * @return string
 */
function get_vendor_directory() {
	return trailingslashit( WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR ) . 'vendor/';
}

/**
 * Returns url to a plugin file.
 *
 * @param string $filepath  Relative path to plugin file.
 *
 * @return string
 */
function plugin_file_url( $filepath ) {
	return plugins_url( $filepath, __FILE__ );
}

/**
 * Checks if WPGraphQL WooCommerce required plugins are installed and activated
 *
 * @param array $deps  Unloaded dependencies list.
 *
 * @return array
 */
function dependencies_not_ready( &$deps = [] ) {
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
 *
 * @return void
 */
function init() {
	// We define this now and pass it as a reference.
	$not_ready = [];

	if ( empty( dependencies_not_ready( $not_ready ) ) ) {
		require_once get_includes_directory() . 'class-wp-graphql-woocommerce.php';
		WP_GraphQL_WooCommerce::instance();
		return;
	}

	foreach ( $not_ready as $dep ) {
		add_action(
			'admin_notices',
			static function () use ( $dep ) {
				?>
				<div class="error notice">
					<p>
						<?php
							printf(
								/* translators: dependency not ready error message */
								esc_html__( '%1$s must be active for "WPGraphQL WooCommerce (WooGraphQL)" to work', 'wp-graphql-woocommerce' ),
								esc_html( $dep )
							);
						?>
					</p>
				</div>
				<?php
			}
		);
	}
}
add_action( 'graphql_init', 'WPGraphQL\WooCommerce\init' );

/**
 * Initializes Protected Router
 *
 * @return void
 */
function init_auth_router() {
	if ( empty( dependencies_not_ready() ) ) {
		require_once get_includes_directory() . 'class-wp-graphql-woocommerce.php';
		WP_GraphQL_WooCommerce::load_auth_router();
	}
}
add_action( 'plugins_loaded', 'WPGraphQL\WooCommerce\init_auth_router' );

// Load constants.
constants();

// Load access functions.
require_once get_plugin_directory() . 'access-functions.php';

// Confirm WC HPOS compatibility.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
