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
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Manage_Stock;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Stock_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Coupon_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Refund_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Variation_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Attribute_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Download_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Rating_Counter_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Customer_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Customer_Address_Type;
use WPGraphQL\Extensions\WooCommerce\Connection\Posts;
use WPGraphQL\Extensions\WooCommerce\Connection\WC_Terms;
use WPGraphQL\Extensions\WooCommerce\Connection\Coupons;
use WPGraphQL\Extensions\WooCommerce\Connection\Products;
use WPGraphQL\Extensions\WooCommerce\Connection\Orders;
use WPGraphQL\Extensions\WooCommerce\Connection\Refunds;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Attributes;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Gallery;
use WPGraphQL\Extensions\WooCommerce\Connection\Customers;
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
				'graphql_register_types',
			),
			10
		);
	}

	/**
	 * Registers WooCommerce types and type_fields to GraphQL schema
	 */
	public static function graphql_register_types() {
		// Enumerations.
		Backorders::register();
		Catalog_Visibility::register();
		Discount_Type::register();
		Manage_Stock::register();
		Stock_Status::register();
		Tax_Status::register();

		// Objects.
		Coupon_Type::register();
		Product_Type::register();
		Product_Variation_Type::register();
		Order_Type::register();
		Refund_Type::register();
		Product_Attribute_Type::register();
		Product_Download_Type::register();
		Product_Rating_Counter_Type::register();
		Customer_Type::register();
		Customer_Address_Type::register();

		// Connections.
		Posts::register_connections();
		WC_Terms::register_connections();
		Coupons::register_connections();
		Products::register_connections();
		Orders::register_connections();
		Refunds::register_connections();
		Product_Attributes::register_connections();
		Customers::register_connections();
	}
}
