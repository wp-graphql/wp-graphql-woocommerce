<?php
/**
 * Registers WooGraphQL types to the schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce;

/**
 * Class Type_Registry
 */
class Type_Registry {
	/**
	 * Registers WooGraphQL types, connections, unions, and mutations to GraphQL schema
	 *
	 * @return void
	 */
	public function init() {
		/**
		 * Enumerations.
		 */
		Type\WPEnum\Backorders::register();
		Type\WPEnum\Catalog_Visibility::register();
		Type\WPEnum\Countries::register();
		Type\WPEnum\Customer_Connection_Orderby_Enum::register();
		Type\WPEnum\Discount_Type::register();
		Type\WPEnum\Manage_Stock::register();
		Type\WPEnum\Order_Status::register();
		Type\WPEnum\Product_Types::register();
		Type\WPEnum\Product_Attribute_Types::register();
		Type\WPEnum\Product_Category_Display::register();
		Type\WPEnum\Stock_Status::register();
		Type\WPEnum\Tax_Class::register();
		Type\WPEnum\Tax_Status::register();
		Type\WPEnum\Tax_Rate_Connection_Orderby_Enum::register();
		Type\WPEnum\Pricing_Field_Format::register();
		Type\WPEnum\Product_Taxonomy::register();
		Type\WPEnum\Taxonomy_Operator::register();
		Type\WPEnum\Post_Type_Orderby_Enum::register();
		Type\WPEnum\Products_Orderby_Enum::register();
		Type\WPEnum\Orders_Orderby_Enum::register();
		Type\WPEnum\Id_Type_Enums::register();
		Type\WPEnum\Cart_Error_Type::register();

		/**
		 * InputObjects.
		 */
		Type\WPInputObject\Cart_Item_Input::register();
		Type\WPInputObject\Customer_Address_Input::register();
		Type\WPInputObject\Product_Attribute_Input::register();
		Type\WPInputObject\Tax_Rate_Connection_Orderby_Input::register();
		Type\WPInputObject\Fee_Line_Input::register();
		Type\WPInputObject\Line_Item_Input::register();
		Type\WPInputObject\Meta_Data_Input::register();
		Type\WPInputObject\Shipping_Line_Input::register();
		Type\WPInputObject\Create_Account_Input::register();
		Type\WPInputObject\Cart_Item_Quantity_Input::register();
		Type\WPInputObject\Product_Taxonomy_Filter_Input::register();
		Type\WPInputObject\Product_Taxonomy_Input::register();
		Type\WPInputObject\Orderby_Inputs::register();

		/**
		 * Interfaces.
		 */
		Type\WPInterface\Product::register_interface();
		Type\WPInterface\Attribute::register_interface();
		Type\WPInterface\Product_Attribute::register_interface();
		Type\WPInterface\Cart_Error::register_interface();
		Type\WPInterface\Payment_Token::register_interface();

		/**
		 * Objects.
		 */
		Type\WPObject\Meta_Data_Type::register();
		Type\WPObject\Downloadable_Item_Type::register();
		Type\WPObject\Coupon_Type::register();
		Type\WPObject\Product_Types::register();
		Type\WPObject\Product_Attribute_Types::register();
		Type\WPObject\Product_Variation_Type::register();
		Type\WPObject\Order_Item_Type::register();
		Type\WPObject\Order_Type::register();
		Type\WPObject\Refund_Type::register();
		Type\WPObject\Product_Download_Type::register();
		Type\WPObject\Customer_Type::register();
		Type\WPObject\Customer_Address_Type::register();
		Type\WPObject\Tax_Rate_Type::register();
		Type\WPObject\Shipping_Method_Type::register();
		Type\WPObject\Cart_Type::register();
		Type\WPObject\Simple_Attribute_Type::register();
		Type\WPObject\Variation_Attribute_Type::register();
		Type\WPObject\Payment_Gateway_Type::register();
		Type\WPObject\Shipping_Package_Type::register();
		Type\WPObject\Shipping_Rate_Type::register();
		Type\WPObject\Cart_Error_Types::register();
		Type\WPObject\Payment_Token_Types::register();
		Type\WPObject\Country_State_Type::register();

		/**
		 * Object fields.
		 */
		Type\WPObject\Product_Category_Type::register_fields();
		Type\WPObject\Root_Query::register_fields();

		// Register the following fields only if "disable_ql_session_handler" option is not on.
		$ql_session_handled_enabled = ! WooCommerce_Filters::is_session_handler_disabled();
		if ( $ql_session_handled_enabled ) {
			Type\WPObject\Customer_Type::register_session_handler_fields();
		}

		// Register the following fields only if "disable_ql_session_handler" option is not "on" and some fields under the "enable_authorizing_url_fields" option are "selected".
		$enabled_url_fields = WooCommerce_Filters::enabled_authorizing_url_fields();
		if ( $ql_session_handled_enabled && ! empty( $enabled_url_fields ) ) {
			Type\WPObject\Customer_Type::register_authorizing_url_fields( array_keys( $enabled_url_fields ) );
		}

		/**
		 * Connections.
		 */
		Connection\Posts::register_connections();
		Connection\WC_Terms::register_connections();
		Connection\Comments::register_connections();
		Connection\Coupons::register_connections();
		Connection\Products::register_connections();
		Connection\Orders::register_connections();
		Connection\Product_Attributes::register_connections();
		Connection\Variation_Attributes::register_connections();
		Connection\Customers::register_connections();
		Connection\Tax_Rates::register_connections();
		Connection\Shipping_Methods::register_connections();
		Connection\Payment_Gateways::register_connections();

		/**
		 * Mutations.
		 */
		Mutation\Customer_Register::register_mutation();
		Mutation\Customer_Update::register_mutation();
		Mutation\Cart_Add_Item::register_mutation();
		Mutation\Cart_Add_Items::register_mutation();
		Mutation\Cart_Update_Item_Quantities::register_mutation();
		Mutation\Cart_Remove_Items::register_mutation();
		Mutation\Cart_Restore_Items::register_mutation();
		Mutation\Cart_Empty::register_mutation();
		Mutation\Cart_Apply_Coupon::register_mutation();
		Mutation\Cart_Remove_Coupons::register_mutation();
		Mutation\Cart_Add_Fee::register_mutation();
		Mutation\Cart_Update_Shipping_Method::register_mutation();
		Mutation\Cart_Fill::register_mutation();
		Mutation\Order_Create::register_mutation();
		Mutation\Order_Update::register_mutation();
		Mutation\Order_Delete::register_mutation();
		Mutation\Order_Delete_Items::register_mutation();
		Mutation\Checkout::register_mutation();
		Mutation\Review_Write::register_mutation();
		Mutation\Review_Update::register_mutation();
		Mutation\Review_Delete_Restore::register_mutation();
		Mutation\Coupon_Create::register_mutation();
		Mutation\Coupon_Update::register_mutation();
		Mutation\Coupon_Delete::register_mutation();
		Mutation\Payment_Method_Delete::register_mutation();
		Mutation\Payment_Method_Set_Default::register_mutation();
		Mutation\Update_Session::register_mutation();
	}
}
