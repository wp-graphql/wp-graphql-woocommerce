<?php
/**
 * Actions
 *
 * Action callbacks for executing actions on the GraphQL Schema
 *
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce;

use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Backorders;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Catalog_Visibility;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Discount_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Stock_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\WC_Post_Object;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Coupon;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Attribute;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Download;
use WPGraphQL\Extensions\WooCommerce\Connection\Coupons;
use WPGraphQL\Extensions\WooCommerce\Connection\Products;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Attributes;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Categories;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Downloads;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Gallery;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Tags;
use WPGraphQL\Extensions\WooCommerce\Data\Loader\WC_Loader;

/**
 * Class Actions
 */
class Actions {
	/**
	 * Loads plugin actions
	 */
	public static function load() {
		add_action(
			'graphql_register_types',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Actions',
				'graphql_register_types'
			),
			10
		);

		add_action(
			'graphql_app_context_additional_loaders',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Actions',
				'graphql_app_context_additional_loaders',
			),
			10
		);
	}

	/**
	 * Registers WooCommerce types and type_fields to GraphQL schema
	 */
	public static function graphql_register_types() {
		/**
		 * Enumerations
		 */
		Backorders::register();
		Catalog_Visibility::register();
		Discount_Type::register();
		Stock_Status::register();
		Tax_Status::register();

		/**
		 * Register fields for WC post_types
		 */
		$wc_post_types = array(
			'shop_coupon',
			'product',
			'product_variation',
			'shop_order',
			'shop_order_refund',
		);
		$allowed_post_types = \WPGraphQL::$allowed_post_types;
		if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
			foreach ( $allowed_post_types as $post_type ) {
				if (in_array( $post_type, $wc_post_types ) ) {
					WC_Post_Object::register( \get_post_type_object( $post_type ) );
				}
			}
		}

		/**
		 * Objects
		 */
		// Product_Attribute::register();
		// Product_Download::register();

		/**
		 * Connections
		 */
		// Coupons::register_connections();
		// Products::register_connections();
		// Product_Attributes::register_connections();
		// Product_Categories::register_connections();
		// Product_Downloads::register_connections();
		// Product_Gallery::register_connections();
		// Product_Tags::register_connections();
	}

	public static function graphql_app_context_additional_loaders( $load ) {
		$load( 'WCLoader', WC_Loader::class );
	}
}
