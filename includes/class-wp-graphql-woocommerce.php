<?php
/**
 * Initializes a singleton instance of WP_GraphQL_WooCommerce
 *
 * @package WPGraphQL\WooCommerce
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce' ) ) :

	/**
	 * Class WP_GraphQL_WooCommerce
	 */
	final class WP_GraphQL_WooCommerce {
		/**
		 * Stores the instance of the WP_GraphQL_WooCommerce class
		 *
		 * @var null|\WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce The one true WP_GraphQL_WooCommerce
		 */
		private static $instance = null;

		/**
		 * Returns a WP_GraphQL_WooCommerce Instance.
		 *
		 * @return \WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->includes();
				self::$instance->setup();
			}

			/**
			 * Fire off init action
			 *
			 * @param \WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce $instance The instance of the WP_GraphQL_WooCommerce class
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
				[
					'product',
					'product_variation',
					'shop_coupon',
					'shop_order',
					'shop_order_refund',
				]
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
				[
					'simple'    => 'SimpleProduct',
					'variable'  => 'VariableProduct',
					'external'  => 'ExternalProduct',
					'grouped'   => 'GroupProduct',
					'variation' => 'ProductVariation',
				]
			);
		}

		/**
		 * Returns GraphQL Product Type name for product types not supported by the GraphQL schema.
		 *
		 * @return string
		 */
		public static function get_supported_product_type() {
			return apply_filters(
				'graphql_woocommerce_unsupported_product_type',
				'UnsupportedProduct'
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
			$attributes = [];
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
		 * @return void
		 */
		private function includes() {
			$include_directory_path = get_includes_directory();

			// Include util class files.
			require $include_directory_path . 'utils/class-ql-session-handler.php';
			require $include_directory_path . 'utils/class-session-transaction-manager.php';

			// Include models class files.
			require $include_directory_path . 'model/class-customer.php';
			require $include_directory_path . 'model/class-wc-post.php';
			require $include_directory_path . 'model/class-coupon.php';
			require $include_directory_path . 'model/class-product.php';
			require $include_directory_path . 'model/class-product-variation.php';
			require $include_directory_path . 'model/class-order.php';
			require $include_directory_path . 'model/class-order-item.php';
			require $include_directory_path . 'model/class-shipping-method.php';
			require $include_directory_path . 'model/class-tax-rate.php';

			// Include data loaders class files.
			require $include_directory_path . 'data/loader/class-wc-cpt-loader.php';
			require $include_directory_path . 'data/loader/class-wc-customer-loader.php';
			require $include_directory_path . 'data/loader/class-wc-db-loader.php';

			// Include connection resolver trait/class files.
			require $include_directory_path . 'data/connection/trait-wc-db-loader-common.php';
			require $include_directory_path . 'data/connection/trait-wc-cpt-loader-common.php';
			require $include_directory_path . 'data/connection/class-cart-item-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-downloadable-item-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-order-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-order-item-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-payment-gateway-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-product-attribute-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-shipping-method-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-tax-rate-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-variation-attribute-connection-resolver.php';

			// Include deprecated resolver trait/class files.
			require $include_directory_path . 'data/connection/class-coupon-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-product-connection-resolver.php';
			require $include_directory_path . 'data/connection/class-customer-connection-resolver.php';

			// Include mutation processor class files.
			require $include_directory_path . 'data/mutation/class-cart-mutation.php';
			require $include_directory_path . 'data/mutation/class-checkout-mutation.php';
			require $include_directory_path . 'data/mutation/class-coupon-mutation.php';
			require $include_directory_path . 'data/mutation/class-customer-mutation.php';
			require $include_directory_path . 'data/mutation/class-order-mutation.php';

			// Include factory class file.
			require $include_directory_path . 'data/class-factory.php';

			// Include DB hooks class files.
			require $include_directory_path . 'data/cursor/class-cot-cursor.php';
			require $include_directory_path . 'data/class-db-hooks.php';

			// Include enum type class files.
			require $include_directory_path . 'type/enum/class-backorders.php';
			require $include_directory_path . 'type/enum/class-cart-error-type.php';
			require $include_directory_path . 'type/enum/class-catalog-visibility.php';
			require $include_directory_path . 'type/enum/class-countries.php';
			require $include_directory_path . 'type/enum/class-customer-connection-orderby-enum.php';
			require $include_directory_path . 'type/enum/class-discount-type.php';
			require $include_directory_path . 'type/enum/class-id-type-enums.php';
			require $include_directory_path . 'type/enum/class-manage-stock.php';
			require $include_directory_path . 'type/enum/class-order-status.php';
			require $include_directory_path . 'type/enum/class-post-type-orderby-enum.php';
			require $include_directory_path . 'type/enum/class-orders-orderby-enum.php';
			require $include_directory_path . 'type/enum/class-products-orderby-enum.php';
			require $include_directory_path . 'type/enum/class-pricing-field-format.php';
			require $include_directory_path . 'type/enum/class-product-attribute-types.php';
			require $include_directory_path . 'type/enum/class-product-category-display.php';
			require $include_directory_path . 'type/enum/class-product-taxonomy.php';
			require $include_directory_path . 'type/enum/class-product-types.php';
			require $include_directory_path . 'type/enum/class-stock-status.php';
			require $include_directory_path . 'type/enum/class-tax-class.php';
			require $include_directory_path . 'type/enum/class-tax-rate-connection-orderby-enum.php';
			require $include_directory_path . 'type/enum/class-tax-status.php';
			require $include_directory_path . 'type/enum/class-taxonomy-operator.php';

			// Include interface type class files.
			require $include_directory_path . 'type/interface/class-attribute.php';
			require $include_directory_path . 'type/interface/class-cart-error.php';
			require $include_directory_path . 'type/interface/class-product-attribute.php';
			require $include_directory_path . 'type/interface/class-product.php';
			require $include_directory_path . 'type/interface/class-payment-token.php';

			// Include object type class files.
			require $include_directory_path . 'type/object/class-cart-error-types.php';
			require $include_directory_path . 'type/object/class-cart-type.php';
			require $include_directory_path . 'type/object/class-coupon-type.php';
			require $include_directory_path . 'type/object/class-customer-address-type.php';
			require $include_directory_path . 'type/object/class-customer-type.php';
			require $include_directory_path . 'type/object/class-downloadable-item-type.php';
			require $include_directory_path . 'type/object/class-meta-data-type.php';
			require $include_directory_path . 'type/object/class-order-item-type.php';
			require $include_directory_path . 'type/object/class-order-type.php';
			require $include_directory_path . 'type/object/class-payment-gateway-type.php';
			require $include_directory_path . 'type/object/class-product-attribute-types.php';
			require $include_directory_path . 'type/object/class-product-category-type.php';
			require $include_directory_path . 'type/object/class-product-download-type.php';
			require $include_directory_path . 'type/object/class-product-types.php';
			require $include_directory_path . 'type/object/class-product-variation-type.php';
			require $include_directory_path . 'type/object/class-refund-type.php';
			require $include_directory_path . 'type/object/class-root-query.php';
			require $include_directory_path . 'type/object/class-shipping-method-type.php';
			require $include_directory_path . 'type/object/class-shipping-package-type.php';
			require $include_directory_path . 'type/object/class-shipping-rate-type.php';
			require $include_directory_path . 'type/object/class-simple-attribute-type.php';
			require $include_directory_path . 'type/object/class-tax-rate-type.php';
			require $include_directory_path . 'type/object/class-variation-attribute-type.php';
			require $include_directory_path . 'type/object/class-payment-token-types.php';
			require $include_directory_path . 'type/object/class-country-state-type.php';

			// Include input type class files.
			require $include_directory_path . 'type/input/class-cart-item-input.php';
			require $include_directory_path . 'type/input/class-cart-item-quantity-input.php';
			require $include_directory_path . 'type/input/class-create-account-input.php';
			require $include_directory_path . 'type/input/class-customer-address-input.php';
			require $include_directory_path . 'type/input/class-fee-line-input.php';
			require $include_directory_path . 'type/input/class-line-item-input.php';
			require $include_directory_path . 'type/input/class-meta-data-input.php';
			require $include_directory_path . 'type/input/class-orderby-inputs.php';
			require $include_directory_path . 'type/input/class-product-attribute-input.php';
			require $include_directory_path . 'type/input/class-product-taxonomy-filter-input.php';
			require $include_directory_path . 'type/input/class-product-taxonomy-input.php';
			require $include_directory_path . 'type/input/class-shipping-line-input.php';
			require $include_directory_path . 'type/input/class-tax-rate-connection-orderby-input.php';

			// Include mutation type class files.
			require $include_directory_path . 'mutation/class-cart-add-fee.php';
			require $include_directory_path . 'mutation/class-cart-add-item.php';
			require $include_directory_path . 'mutation/class-cart-add-items.php';
			require $include_directory_path . 'mutation/class-cart-apply-coupon.php';
			require $include_directory_path . 'mutation/class-cart-empty.php';
			require $include_directory_path . 'mutation/class-cart-fill.php';
			require $include_directory_path . 'mutation/class-cart-remove-coupons.php';
			require $include_directory_path . 'mutation/class-cart-remove-items.php';
			require $include_directory_path . 'mutation/class-cart-restore-items.php';
			require $include_directory_path . 'mutation/class-cart-update-item-quantities.php';
			require $include_directory_path . 'mutation/class-cart-update-shipping-method.php';
			require $include_directory_path . 'mutation/class-checkout.php';
			require $include_directory_path . 'mutation/class-coupon-create.php';
			require $include_directory_path . 'mutation/class-coupon-delete.php';
			require $include_directory_path . 'mutation/class-coupon-update.php';
			require $include_directory_path . 'mutation/class-customer-register.php';
			require $include_directory_path . 'mutation/class-customer-update.php';
			require $include_directory_path . 'mutation/class-order-create.php';
			require $include_directory_path . 'mutation/class-order-delete-items.php';
			require $include_directory_path . 'mutation/class-order-delete.php';
			require $include_directory_path . 'mutation/class-order-update.php';
			require $include_directory_path . 'mutation/class-review-write.php';
			require $include_directory_path . 'mutation/class-review-delete-restore.php';
			require $include_directory_path . 'mutation/class-review-update.php';
			require $include_directory_path . 'mutation/class-payment-method-delete.php';
			require $include_directory_path . 'mutation/class-payment-method-set-default.php';
			require $include_directory_path . 'mutation/class-update-session.php';

			// Include connection class/function files.
			require $include_directory_path . 'connection/wc-cpt-connection-args.php';
			require $include_directory_path . 'connection/class-comments.php';
			require $include_directory_path . 'connection/class-coupons.php';
			require $include_directory_path . 'connection/class-customers.php';
			require $include_directory_path . 'connection/class-orders.php';
			require $include_directory_path . 'connection/class-payment-gateways.php';
			require $include_directory_path . 'connection/class-posts.php';
			require $include_directory_path . 'connection/class-product-attributes.php';
			require $include_directory_path . 'connection/class-products.php';
			require $include_directory_path . 'connection/class-shipping-methods.php';
			require $include_directory_path . 'connection/class-tax-rates.php';
			require $include_directory_path . 'connection/class-variation-attributes.php';
			require $include_directory_path . 'connection/class-wc-terms.php';

			// Include admin files.
			require $include_directory_path . 'admin/class-section.php';
			require $include_directory_path . 'admin/class-general.php';

			// Include main plugin class files.
			require $include_directory_path . 'class-admin.php';
			require $include_directory_path . 'class-core-schema-filters.php';
			require $include_directory_path . 'class-jwt-auth-schema-filters.php';
			require $include_directory_path . 'class-woocommerce-filters.php';
			require $include_directory_path . 'class-acf-schema-filters.php';
			require $include_directory_path . 'class-type-registry.php';

			// Required extra plugin function file.
			require $include_directory_path . 'functions.php';

			/**
			 * WPGRAPHQL_AUTOLOAD can be set to "false" to prevent the autoloader from running.
			 * In most cases, this is not something that should be disabled, but some environments
			 * may bootstrap their dependencies in a global autoloader that will autoload files
			 * before we get to this point, and requiring the autoloader again can trigger fatal errors.
			 *
			 * The codeception tests are an example of an environment where adding the autoloader again causes issues
			 * so this is set to false for tests.
			 */
			if ( defined( 'WPGRAPHQL_WOOCOMMERCE_AUTOLOAD' ) && true === WPGRAPHQL_WOOCOMMERCE_AUTOLOAD ) {
				if ( file_exists( get_vendor_directory() . 'autoload.php' ) ) {
					// Autoload Required Classes.
					require_once get_vendor_directory() . 'autoload.php';
				}

				/**
				 * If GraphQL class doesn't exist, then dependencies cannot be
				 * detected. This likely means the user cloned the repo from Github
				 * but did not run `composer install`
				 */
				if ( ! class_exists( 'WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT' ) ) {
					add_action(
						'admin_notices',
						static function () {
							if ( ! current_user_can( 'manage_options' ) ) {
								return;
							}

							echo sprintf(
								'<div class="notice notice-error">' .
								'<p>%s</p>' .
								'</div>',
								esc_html__(
									'WooGraphQL appears to have been installed without it\'s dependencies. It will not work properly until dependencies are installed. This likely means you have cloned WPGraphQL from Github and need to run the command `composer install`.',
									'wp-graphql-woocommerce'
								)
							);
						}
					);
				}
			}//end if
		}

		/**
		 * Returns true if any authorizing urls are enabled.
		 *
		 * @return array
		 */
		public static function get_enabled_auth_urls() {
			return woographql_setting( 'enable_authorizing_url_fields', [] );
		}

		/**
		 * Returns true if any authorizing urls are enabled.
		 *
		 * @return bool
		 */
		public static function auth_router_is_enabled() {
			return defined( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' )
				|| ! empty( self::get_enabled_auth_urls() );
		}

		/**
		 * Import and setups Protected_Router class instance.
		 *
		 * @return void
		 */
		public static function load_auth_router() {
			require get_includes_directory() . 'utils/class-protected-router.php';
			add_action( 'after_setup_theme', [ Utils\Protected_Router::class, 'initialize' ] );
		}

		/**
		 * Sets up WooGraphQL schema.
		 *
		 * @return void
		 */
		private function setup() {
			// Initialize WooGraphQL Settings.
			new Admin();

			// Initialize WooGraphQL DB hooks.
			new Data\DB_Hooks();

			// Setup minor integrations.
			Functions\setup_minor_integrations();

			// Register WooCommerce filters.
			WooCommerce_Filters::setup();

			// Register WPGraphQL core filters.
			Core_Schema_Filters::add_filters();

			// Register WPGraphQL ACF filters.
			ACF_Schema_Filters::add_filters();

			// Register WPGraphQL JWT Authentication filters.
			JWT_Auth_Schema_Filters::add_filters();

			// Initialize WooGraphQL TypeRegistry.
			$registry = new Type_Registry();
			add_action( 'graphql_register_types', [ $registry, 'init' ] );
		}
	}

endif;
