<?php
/**
 * WPEnum Type - CartErrorType
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.8.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Cart_Error_Type
 */
class Cart_Error_Type {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = array(
			'INVALID_CART_ITEM'       => array( 'value' => 'INVALID_CART_ITEM' ),
			'INVALID_COUPON'          => array( 'value' => 'INVALID_COUPON' ),
			'INVALID_SHIPPING_METHOD' => array( 'value' => 'INVALID_SHIPPING_METHOD' ),
		);

		register_graphql_enum_type(
			'CartErrorType',
			array(
				'description' => __( 'Cart error type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
