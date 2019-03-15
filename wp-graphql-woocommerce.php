<?php
/**
 * Plugin Name:     WP GraphQL WooCommerce
 * Plugin URI:      https://developer.axistaylor.com/wp-graphql-woocommerce
 * Description:     Adds Woocommerce types to WPGraphQL schema.
 * Author:          kidunot89
 * Author URI:      https://axistaylor.com
 * Text Domain:     wp-graphql-woocommerce
 * Domain Path:     /languages
 * Version:         0.0.1
 * Requires at least: 4.7.0
 * Tested up to: 4.7.1
 * Requires PHP: 5.5
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package		wp-graphql-woocommerce
 * @category	Extension
 * @author		kidunot89
 * @version		0.0.1
 */

namespace WPGraphQL\Extensions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * If the codeception remote coverage file exists, require it.
 *
 * This file should only exist locally or when CI bootstraps the environment for testing
 */
if ( file_exists( __DIR__ . '/c3.php' ) ) {
	require_once( __DIR__ . '/c3.php' );
}

if ( ! class_exists( '\WPGraphQL\Extensions\WPGraphQLWooCommerce' ) ) :
		final class WPGraphQLWooCommerce {
			/**
			 * Stores the instance of the WPGraphQL\Extensions\WPGraphQLWooCommerce class
			 *
			 * @var WPGraphQLWooCommerce The one true WPGraphQL\Extensions\WPGraphQLWooCommerce
			 * @access private
			 */
			private static $instance;

			public static function instance() {
				if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPGraphQLWooCommerce ) ) {
					self::$instance = new WPGraphQLWooCommerce;
					self::$instance->setup_constants();
					self::$instance->includes();
					self::$instance->actions();
				}

				/**
				 * Fire off init action
				 *
				 * @param WPGraphQLWooCommerce $instance The instance of the WPGraphQLWooCommerce class
				 */
				do_action( 'graphql_woocommerce_init', self::$instance );

				/**
				 * Return the WPGraphQLWooCommerce Instance
				 */
				return self::$instance;
			}

			/**
			 * Throw error on object clone.
			 * The whole idea of the singleton design pattern is that there is a single object
			 * therefore, we don't want the object to be cloned.
			 *
			 * @since  0.0.1
			 * @access public
			 * @return void
			 */
			public function __clone() {
				// Cloning instances of the class is forbidden.
				_doing_it_wrong( __FUNCTION__, esc_html__( 'WPGraphQLWooCommerce class should not be cloned.', 'wp-graphql-woocommerce' ), '0.0.1' );
			}

			/**
			 * Disable unserializing of the class.
			 *
			 * @since  0.0.1
			 * @access protected
			 * @return void
			 */
			public function __wakeup() {
				// De-serializing instances of the class is forbidden.
				_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQLWooCommerce class is not allowed', 'wp-graphql-woocommerce' ), '0.0.1' );
			}

			/**
			 * Setup plugin constants.
			 *
			 * @access private
			 * @since  0.0.1
			 * @return void
			 */
			private function setup_constants() {
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
				// Whether to autoload the files or not
				if ( ! defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) ) {
					define( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD', true );
				}
			}

			/**
			 * Include required files.
			 * Uses composer's autoload
			 *
			 * @access private
			 * @since  0.0.1
			 * @return void
			 */
			private function includes() {
				// Autoload Required Classes
				if ( defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) && true == WPGRAPHQL_WOOCOMMERCE_AUTOLOAD ) {
					require_once( WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php' );
				}
			}

			/**
			 * Sets up actions to run at certain spots throughout WordPress and the WPGraphQL execution cycle
			 */
			private function actions() {
				
			}
		}
endif;

function dependencies_not_ready() {
	$deps = [];
	if ( ! class_exists( '\WPGraphQL' ) ) {
		$deps[] = 'WPGraphQL';
	}
	if ( ! class_exists( '\WooCommerce' ) ) {
		$deps[] = 'WooCommerce';
	}

	return $deps;
}

function initWPGraphQLWooCommerce() {
	$not_ready = dependencies_not_ready();
	if ( empty( $not_ready ) ) {
		return WPGraphQLWooCommerce::instance();
	}

	foreach( $not_ready as $dep ) {
		add_action( 
			'admin_notices',
			function() { ?>
				<div class="error notice">
					<p><?php _e( "$dep must be active for wp-graphql-woocommerce to work", 'wp-graphql-woocommerce' ); ?></p>
				</div>
			<?php }
		);
	}

	return false;
}
add_action( 'graphql_init', '\WPGraphQL\Extensions\initWPGraphQLWooCommerce' );