<?php
/**
 * Initializes a singleton instance of WP_GraphQL_WooCommerce
 *
 * @package WPGraphQL\WooCommerce
 * @since 0.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_GraphQL_WooCommerce' ) ) :

	/**
	 * Class WP_GraphQL_WooCommerce
	 */
	final class WP_GraphQL_WooCommerce {

		/**
		 * Stores the instance of the WP_GraphQL_WooCommerce class
		 *
		 * @var WP_GraphQL_WooCommerce The one true WP_GraphQL_WooCommerce
		 */
		private static $instance;

		/**
		 * Returns a WP_GraphQL_WooCommerce Instance.
		 *
		 * @return WP_GraphQL_WooCommerce
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( is_a( self::$instance, __CLASS__ ) ) ) {
				self::$instance = new self();
				self::$instance->includes();
				self::$instance->setup();
			}

			/**
			 * Fire off init action
			 *
			 * @param WP_GraphQL_WooCommerce $instance The instance of the WP_GraphQL_WooCommerce class
			 */
			do_action( 'graphql_woocommerce_init', self::$instance );

			// Return the WPGraphQLWooCommerce Instance.
			return self::$instance;
		}

		/**
		 * Returns WooCommerce post-types registered to the WC_Post_Crud_Loader
		 *
		 * @return array
		 */
		public static function get_post_types() {
			return apply_filters(
				'graphql_woocommerce_post_types',
				array(
					'product',
					'product_variation',
					'shop_coupon',
					'shop_order',
					'shop_order_refund',
				)
			);
		}

		/**
		 * Returns WooCommerce product types to be exposed to the GraphQL schema.
		 *
		 * @return array
		 */
		public static function get_enabled_product_types() {
			return apply_filters(
				'graphql_woocommerce_product_types',
				array(
					'simple'   => 'SimpleProduct',
					'variable' => 'VariableProduct',
					'external' => 'ExternalProduct',
					'grouped'  => 'GroupProduct',
				)
			);
		}

		/**
		 * Returns WooCommerce product attribute taxonomies to be registered as
		 * "TermObject" types in the schema.
		 *
		 * @return array
		 */
		public static function get_product_attribute_taxonomies() {
			$attribute_taxonomies = \wc_get_attribute_taxonomies();

			// Get taxonomy names.
			$attributes = array();
			foreach ( $attribute_taxonomies as $tax ) {
				$attributes[] = 'pa_' . $tax->attribute_name;
			}

			/**
			 * Filter the $attributes to allow the removal or addition of product attribute taxonomies
			 *
			 * @param array $attributes Product attributes being passed.
			 */
			return apply_filters(
				'graphql_woocommerce_product_attributes_taxonomies',
				$attributes
			);
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @since  0.0.1
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'WP_GraphQL_WooCommerce class should not be cloned.', 'wp-graphql-woocommerce' ), '0.0.1' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since  0.0.1
		 */
		public function __wakeup() {
			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WP_GraphQL_WooCommerce class is not allowed', 'wp-graphql-woocommerce' ), '0.0.1' );
		}

		/**
		 * Include required files.
		 * Uses composer's autoload
		 *
		 * @since  0.0.1
		 */
		private function includes() {

			// Autoload Required Classes.
			if ( defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) && false !== WPGRAPHQL_WOOCOMMERCE_AUTOLOAD ) {
				require_once WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php';
			}

			// Required non-autoloaded classes.
			require_once WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR . 'access-functions.php';
			require_once WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR . 'class-inflect.php';
		}

		/**
		 * Sets up WooGraphQL schema.
		 */
		private function setup() {
			// Setup minor integrations.
			\WPGraphQL\WooCommerce\Functions\setup_minor_integrations();

			// Register WooCommerce filters.
			\WPGraphQL\WooCommerce\WooCommerce_Filters::setup();

			// Register WPGraphQL core filters.
			\WPGraphQL\WooCommerce\Core_Schema_Filters::add_filters();

			// Register WPGraphQL ACF filters.
			\WPGraphQL\WooCommerce\ACF_Schema_Filters::add_filters();

			// Register WPGraphQL JWT Authentication filters.
			\WPGraphQL\WooCommerce\JWT_Auth_Schema_Filters::add_filters();

			// Initialize WooGraphQL TypeRegistry.
			$registry = new \WPGraphQL\WooCommerce\Type_Registry();
			add_action( 'graphql_register_types', array( $registry, 'init' ), 10, 1 );
		}
	}

endif;
