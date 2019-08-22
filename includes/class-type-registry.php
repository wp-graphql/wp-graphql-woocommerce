<?php
/**
 * Registers WooGraphQL types to the schema.
 *
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce;

/**
 * Class Type_Registry
 */
class Type_Registry {
	/**
	 * Registers actions related to type registry.
	 */
	public static function add_actions() {
		// Register types.
		add_action( 'graphql_register_types', array( __CLASS__, 'graphql_register_types' ), 10 );
	}

	/**
	 * Registers WooGraphQL types, connection, and mutations to GraphQL schema
	 */
	public static function graphql_register_types() {
		// Enumerations.
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Backorders::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Catalog_Visibility::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Countries::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Customer_Connection_Orderby_Enum::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Discount_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Manage_Stock::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Order_Status::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Product_Types::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Stock_Status::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Class::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Status::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\WC_Connection_Orderby_Enum::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Tax_Rate_Connection_Orderby_Enum::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Pricing_Field_Format::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Product_Taxonomy::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Taxonomy_Operator::register();

		// InputObjects.
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Customer_Address_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Product_Attribute_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\WC_Connection_Orderby_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Tax_Rate_Connection_Orderby_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Fee_Line_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Line_Item_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Meta_Data_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Shipping_Line_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Create_Account_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Cart_Item_Quantity_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Product_Taxonomy_Filter_Input::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Product_Taxonomy_Filter_Relation_Input::register();

		// Objects.
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Coupon_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Variation_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Item_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Refund_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Attribute_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Download_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Customer_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Customer_Address_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Tax_Rate_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Shipping_Method_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Cart_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Variation_Attribute_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Payment_Gateway_Type::register();
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Meta_Data_Type::register();

		// Object fields.
		\WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Category_Type::register_fields();

		// Connections.
		\WPGraphQL\Extensions\WooCommerce\Connection\Posts::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\WC_Terms::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Coupons::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Products::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Orders::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Order_Items::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Refunds::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Product_Attributes::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Variation_Attributes::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Customers::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Tax_Rates::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Shipping_Methods::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Cart_Items::register_connections();
		\WPGraphQL\Extensions\WooCommerce\Connection\Payment_Gateways::register_connections();

		// Mutations.
		\WPGraphQL\Extensions\WooCommerce\Mutation\Customer_Register::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Customer_Update::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Add_Item::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Update_Item_Quantities::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Remove_Items::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Restore_Items::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Empty::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Apply_Coupon::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Remove_Coupons::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Cart_Add_Fee::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Order_Create::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Order_Update::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Order_Delete::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Order_Delete_Items::register_mutation();
		\WPGraphQL\Extensions\WooCommerce\Mutation\Checkout::register_mutation();
	}
}
