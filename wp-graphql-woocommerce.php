<?php
/**
 * Plugin Name: WPGraphQL WooCommerce (WooGraphQL)
 * Plugin URI: https://github.com/wp-graphql/wp-graphql-woocommerce
 * Description: Adds Woocommerce Functionality to WPGraphQL schema.
 * Version: 0.10.5
 * Author: kidunot89
 * Author URI: https://axistaylor.com
 * Text Domain: wp-graphql-woocommerce
 * Domain Path: /languages
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 4.8.0
 * WC tested up to: 5.3.0
 * WPGraphQL requires at least: 1.6.1+
 * WPGraphQL-JWT-Authentication requires at least: 0.4.0+
 *
 * @package     WPGraphQL\WooCommerce
 * @author      kidunot89
 * @license     GPL-3
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setups WPGraphQL WooCommerce constants
 */
function woographql_constants() {
	// Plugin version.
	if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_VERSION' ) ) {
		define( 'WPGRAPHQL_WOOCOMMERCE_VERSION', '0.10.4' );
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
function woographql_dependencies_not_ready() {
	$deps = array();
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
function woographql_init() {
	woographql_constants();

	$not_ready = woographql_dependencies_not_ready();
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

	return false;
}
add_action( 'graphql_init', 'woographql_init' );
