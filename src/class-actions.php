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

/**
 * Class Actions
 */
class Actions {
	/**
	 * Loads plugin actions
	 */
	public static function load() {
		add_action( 'graphql_register_types', array( '\WPGraphQL\Extensions\WooCommerce\Actions', 'register_types'	), 10 );
	}

	/**
	 * Registers types to GraphQL schema
	 */
	public static function register_types ()
	{
		// Enumerations
		Backorders::register();
		Catalog_Visibility::register();
		Discount_Type::register();
		Stock_Status::register();
		Tax_Status::register();

		// Objects
		Coupon::register();
		Product::register();
		Product_Attribute::register();
		Product_Download::register();

		// Connections
		Coupons::register_connections();
		Products::register_connections();
		Product_Attributes::register_connections();
		Product_Categories::register_connections();
		Product_Downloads::register_connections();
		Product_Gallery::register_connections();
		Product_Tags::register_connections();
	}
}
