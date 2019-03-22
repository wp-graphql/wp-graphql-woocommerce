<?php

namespace WPGraphQL\Extensions\WooCommerce;

use WPGraphQL\Extensions\WooCommerce\Type\Enum\Backorders;
use WPGraphQL\Extensions\WooCommerce\Type\Enum\Catalog_Visibility;
use WPGraphQL\Extensions\WooCommerce\Type\Enum\Discount_Type;
use WPGraphQL\Extensions\WooCommerce\Type\Enum\Stock_Status;
use WPGraphQL\Extensions\WooCommerce\Type\Enum\Tax_Status;
use WPGraphQL\Extensions\WooCommerce\Type\Object\Coupon;
use WPGraphQL\Extensions\WooCommerce\Type\Object\Product;
use WPGraphQL\Extensions\WooCommerce\Type\Object\Product_Attribute;
use WPGraphQL\Extensions\WooCommerce\Type\Object\Product_Download;
use WPGraphQL\Extensions\WooCommerce\Connection\Coupons;
use WPGraphQL\Extensions\WooCommerce\Connection\Products;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Attributes;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Categories;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Downloads;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Gallery;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Tags;

/**
 * Class Actions
 *
 * static functions for executing actions on the GraphQL Schema
 *
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
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
