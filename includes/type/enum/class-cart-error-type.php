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
	 *
	 * @return void
	 */
	public static function register() {
		$values = [
			'INVALID_CART_ITEM'       => [ 'value' => 'INVALID_CART_ITEM' ],
			'INVALID_COUPON'          => [ 'value' => 'INVALID_COUPON' ],
			'INVALID_SHIPPING_METHOD' => [ 'value' => 'INVALID_SHIPPING_METHOD' ],
			'UNKNOWN'                 => [ 'value' => 'UNKNOWN' ],
		];

		register_graphql_enum_type(
			'CartErrorType',
			[
				'description' => __( 'Cart error type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
