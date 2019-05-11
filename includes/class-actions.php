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
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Countries;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Customer_Connection_Orderby_Enum;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Discount_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Manage_Stock;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Order_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Product_Types;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Stock_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Class;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Status;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\WC_Connection_Orderby_Enum;
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Rate_Connection_Orderby_Enum;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Customer_Address_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Product_Attribute_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\WC_Connection_Orderby_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Tax_Rate_Connection_Orderby_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Coupon_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Item_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Refund_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Variation_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Attribute_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Download_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Rating_Counter_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Customer_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Customer_Address_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Tax_Rate_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Shipping_Method_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Cart_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Variation_Attribute_Type;
use WPGraphQL\Extensions\WooCommerce\Connection\Posts;
use WPGraphQL\Extensions\WooCommerce\Connection\WC_Terms;
use WPGraphQL\Extensions\WooCommerce\Connection\Coupons;
use WPGraphQL\Extensions\WooCommerce\Connection\Products;
use WPGraphQL\Extensions\WooCommerce\Connection\Orders;
use WPGraphQL\Extensions\WooCommerce\Connection\Order_Items;
use WPGraphQL\Extensions\WooCommerce\Connection\Refunds;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Attributes;
use WPGraphQL\Extensions\WooCommerce\Connection\Variation_Attributes;
use WPGraphQL\Extensions\WooCommerce\Connection\Product_Gallery;
use WPGraphQL\Extensions\WooCommerce\Connection\Customers;
use WPGraphQL\Extensions\WooCommerce\Connection\Tax_Rates;
use WPGraphQL\Extensions\WooCommerce\Connection\Shipping_Methods;
use WPGraphQL\Extensions\WooCommerce\Connection\Cart_Items;
use WPGraphQL\Extensions\WooCommerce\Mutation\Customer_Register;
use WPGraphQL\Extensions\WooCommerce\Mutation\Customer_Update;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Add_Item;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Update_Item_Quantity;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Remove_Items;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Restore_Items;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Empty;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Apply_Coupon;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Remove_Coupons;
use WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Add_Fee;

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
		Countries::register();
		Customer_Connection_Orderby_Enum::register();
		Discount_Type::register();
		Manage_Stock::register();
		Order_Status::register();
		Product_Types::register();
		Stock_Status::register();
		Tax_Class::register();
		Tax_Status::register();
		WC_Connection_Orderby_Enum::register();
		Tax_Rate_Connection_Orderby_Enum::register();

		// InputObjects.
		Customer_Address_Input::register();
		Product_Attribute_Input::register();
		WC_Connection_Orderby_Input::register();
		Tax_Rate_Connection_Orderby_Input::register();

		// Objects.
		Coupon_Type::register();
		Product_Type::register();
		Product_Variation_Type::register();
		Order_Type::register();
		Order_Item_Type::register();
		Refund_Type::register();
		Product_Attribute_Type::register();
		Product_Download_Type::register();
		Product_Rating_Counter_Type::register();
		Customer_Type::register();
		Customer_Address_Type::register();
		Tax_Rate_Type::register();
		Shipping_Method_Type::register();
		Cart_Type::register();
		Variation_Attribute_Type::register();

		// Connections.
		Posts::register_connections();
		WC_Terms::register_connections();
		Coupons::register_connections();
		Products::register_connections();
		Orders::register_connections();
		Order_Items::register_connections();
		Refunds::register_connections();
		Product_Attributes::register_connections();
		Variation_Attributes::register_connections();
		Customers::register_connections();
		Tax_Rates::register_connections();
		Shipping_Methods::register_connections();
		Cart_Items::register_connections();

		// Mutations.
		Customer_Register::register_mutation();
		Customer_Update::register_mutation();
		Cart_Add_Item::register_mutation();
		Cart_Update_Item_Quantity::register_mutation();
		Cart_Remove_Items::register_mutation();
		Cart_Restore_Items::register_mutation();
		Cart_Empty::register_mutation();
		Cart_Apply_Coupon::register_mutation();
		Cart_Remove_Coupons::register_mutation();
		Cart_Add_Fee::register_mutation();
	}
}
