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

use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
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
use WPGraphQL\Extensions\WooCommerce\Type\WPEnum\Pricing_Field_Format;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Customer_Address_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Product_Attribute_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\WC_Connection_Orderby_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Tax_Rate_Connection_Orderby_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Fee_Line_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Line_Item_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Meta_Data_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Shipping_Line_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPInputObject\Create_Account_Input;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Coupon_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Order_Item_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Refund_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Variation_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Attribute_Type;
use WPGraphQL\Extensions\WooCommerce\Type\WPObject\Product_Download_Type;
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
use WPGraphQL\Extensions\WooCommerce\Mutation\Order_Create;
use WPGraphQL\Extensions\WooCommerce\Mutation\Order_Update;
use WPGraphQL\Extensions\WooCommerce\Mutation\Order_Delete;
use WPGraphQL\Extensions\WooCommerce\Mutation\Checkout;

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
		Pricing_Field_Format::register();

		// InputObjects.
		Customer_Address_Input::register();
		Product_Attribute_Input::register();
		WC_Connection_Orderby_Input::register();
		Tax_Rate_Connection_Orderby_Input::register();
		Fee_Line_Input::register();
		Line_Item_Input::register();
		Meta_Data_Input::register();
		Shipping_Line_Input::register();
		Create_Account_Input::register();

		// Objects.
		Coupon_Type::register();
		Product_Type::register();
		Product_Variation_Type::register();
		Order_Type::register();
		Order_Item_Type::register();
		Refund_Type::register();
		Product_Attribute_Type::register();
		Product_Download_Type::register();
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
		Order_Create::register_mutation();
		Order_Update::register_mutation();
		Order_Delete::register_mutation();
		Checkout::register_mutation();

		register_graphql_field(
			'ProductCategory',
			'image',
			array(
				'type'        => 'MediaItem',
				'description' => __( 'Product category image', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context ) {
					$thumbnail_id = get_term_meta( $source->term_id, 'thumbnail_id', true );
					return ! empty( $thumbnail_id )
						? DataSource::resolve_post_object( $thumbnail_id, $context )
						: null;
				},
			)
		);

		if ( class_exists( '\WPGraphQL\JWT_Authentication\ManageTokens' ) ) {
			$fields = array();
			foreach ( \WPGraphQL\JWT_Authentication\ManageTokens::add_user_fields() as $field_name => $field ) {
				$root_resolver         = $field['resolve'];
				$fields[ $field_name ] = array_merge(
					$field,
					array(
						'resolve' => function( $source ) use ( $root_resolver ) {
							$user = get_user_by( 'id', $source->ID );
							return $root_resolver( $user );
						},
					)
				);
			}
			register_graphql_fields(
				'Customer',
				$fields
			);
		}
	}
}
