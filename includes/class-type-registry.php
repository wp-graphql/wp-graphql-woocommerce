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
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry  Instance of the WPGraphQL TypeRegistry.
	 */
	public function init( \WPGraphQL\Registry\TypeRegistry $type_registry ) {
		// Enumerations.
		\WPGraphQL\WooCommerce\Type\WPEnum\Backorders::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Catalog_Visibility::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Countries::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Customer_Connection_Orderby_Enum::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Discount_Type::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Manage_Stock::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Order_Status::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Product_Types::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Product_Attribute_Types::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Product_Category_Display::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Stock_Status::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Tax_Class::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Tax_Status::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Tax_Rate_Connection_Orderby_Enum::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Pricing_Field_Format::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Product_Taxonomy::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Taxonomy_Operator::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Post_Type_Orderby_Enum::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Products_Orderby_Enum::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Orders_Orderby_Enum::register();
		\WPGraphQL\WooCommerce\Type\WPEnum\Id_Type_Enums::register();

		// InputObjects.
		\WPGraphQL\WooCommerce\Type\WPInputObject\Customer_Address_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Product_Attribute_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Tax_Rate_Connection_Orderby_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Fee_Line_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Line_Item_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Meta_Data_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Shipping_Line_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Create_Account_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Cart_Item_Quantity_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Product_Taxonomy_Filter_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Product_Taxonomy_Filter_Relation_Input::register();
		\WPGraphQL\WooCommerce\Type\WPInputObject\Orderby_Inputs::register();

		// Interfaces.
		\WPGraphQL\WooCommerce\Type\WPInterface\Product::register_interface( $type_registry );
		\WPGraphQL\WooCommerce\Type\WPInterface\Product_Attribute::register_interface( $type_registry );

		// Objects.
		\WPGraphQL\WooCommerce\Type\WPObject\Coupon_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Product_Types::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Product_Attribute_Types::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Product_Variation_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Order_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Order_Item_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Downloadable_Item_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Refund_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Product_Download_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Customer_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Customer_Address_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Tax_Rate_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Shipping_Method_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Cart_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Variation_Attribute_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Payment_Gateway_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Meta_Data_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Shipping_Package_Type::register();
		\WPGraphQL\WooCommerce\Type\WPObject\Shipping_Rate_Type::register();

		// Object fields.
		\WPGraphQL\WooCommerce\Type\WPObject\Product_Category_Type::register_fields();
		\WPGraphQL\WooCommerce\Type\WPObject\Root_Query::register_fields();

		// Connections.
		\WPGraphQL\WooCommerce\Connection\Posts::register_connections();
		\WPGraphQL\WooCommerce\Connection\WC_Terms::register_connections();
		\WPGraphQL\WooCommerce\Connection\Comments::register_connections();
		\WPGraphQL\WooCommerce\Connection\Coupons::register_connections();
		\WPGraphQL\WooCommerce\Connection\Products::register_connections();
		\WPGraphQL\WooCommerce\Connection\Orders::register_connections();
		\WPGraphQL\WooCommerce\Connection\Order_Items::register_connections();
		\WPGraphQL\WooCommerce\Connection\Downloadable_Items::register_connections();
		\WPGraphQL\WooCommerce\Connection\Refunds::register_connections();
		\WPGraphQL\WooCommerce\Connection\Product_Attributes::register_connections();
		\WPGraphQL\WooCommerce\Connection\Variation_Attributes::register_connections();
		\WPGraphQL\WooCommerce\Connection\Customers::register_connections();
		\WPGraphQL\WooCommerce\Connection\Tax_Rates::register_connections();
		\WPGraphQL\WooCommerce\Connection\Shipping_Methods::register_connections();
		\WPGraphQL\WooCommerce\Connection\Cart_Items::register_connections();
		\WPGraphQL\WooCommerce\Connection\Payment_Gateways::register_connections();

		// Mutations.
		\WPGraphQL\WooCommerce\Mutation\Customer_Register::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Customer_Update::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Add_Item::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Update_Item_Quantities::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Remove_Items::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Restore_Items::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Empty::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Apply_Coupon::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Remove_Coupons::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Add_Fee::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Cart_Update_Shipping_Method::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Order_Create::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Order_Update::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Order_Delete::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Order_Delete_Items::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Checkout::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Review_Write::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Review_Update::register_mutation();
		\WPGraphQL\WooCommerce\Mutation\Review_Delete_Restore::register_mutation();
	}
}
