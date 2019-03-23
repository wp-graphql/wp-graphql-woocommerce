<?php
/**
 * WP_GraphQL_WooCommerce
 * 
 * Initializes a singleton instance of WP_GraphQL_WooCommerce
 * 
 * @package WPGraphQL\Extensions\WooCommerce
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
		 * Stores the instance of the WPGraphQL\Extensions\WPGraphQLWooCommerce class
		 *
		 * @var WPGraphQLWooCommerce The one true WPGraphQL\Extensions\WPGraphQLWooCommerce
		 * @access private
		 */
		private static $instance;

		/**
		 * Singleton provider
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPGraphQLWooCommerce ) ) {
				self::$instance = new WP_GraphQL_WooCommerce();
				self::$instance->includes();
				self::$instance->actions();
				self::$instance->filters();
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'WP_GraphQL_WooCommerce class should not be cloned.', 'wp-graphql-woocommerce' ), '0.0.1' );
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WP_GraphQL_WooCommerce class is not allowed', 'wp-graphql-woocommerce' ), '0.0.1' );
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
				require_once WPGRAPHQL_WOOCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Sets up actions to run at certain spots throughout WordPress and the WPGraphQL execution cycle
		 */
		private function actions() {
			/**
			 * Setup actions
			 */
			\WPGraphQL\Extensions\WooCommerce\Actions::load();
		}

		/**
		 * Sets up filters to run at certain spots throughout WordPress and the WPGraphQL execution cycle
		 */
		private function filters() {
			/**
			 * Registers WooCommerce taxonomies to be shown in GraphQL
			 */
			add_filter( 'register_taxonomy_args', [ $this, 'taxonomies' ], 10, 2 );

			/**
			 * Setup filters
			 */
			\WPGraphQL\Extensions\WooCommerce\Filters::load();
		}

		/**
		 * Determine the taxonomies that should show in GraphQL
		 */
		public function taxonomies( $args, $taxonomy ) {
			if ( 'product_type' === $taxonomy ) {
				$args['show_in_graphql']     = true;
				$args['graphql_single_name'] = 'productType';
				$args['graphql_plural_name'] = 'productTypes';
			}

			if ( 'product_visibility' === $taxonomy ) {
				$args['show_in_graphql']     = true;
				$args['graphql_single_name'] = 'visibleProduct';
				$args['graphql_plural_name'] = 'visibleProducts';
			}

			if ( 'product_cat' === $taxonomy ) {
				$args['show_in_graphql']     = true;
				$args['graphql_single_name'] = 'productCategory';
				$args['graphql_plural_name'] = 'productCategories';
			}

			if ( 'product_tag' === $taxonomy ) {
				$args['show_in_graphql']     = true;
				$args['graphql_single_name'] = 'productTag';
				$args['graphql_plural_name'] = 'productTags';
			}

			if ( 'product_shipping_class' === $taxonomy ) {
				$args['show_in_graphql']     = true;
				$args['graphql_single_name'] = 'shippingClass';
				$args['graphql_plural_name'] = 'shippingClasses';
			}

			return $args;
		}
	}
endif;