<?php
/**
 * WPEnum Type - DiscountTypeEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Discount_Type
 */
class Discount_Type {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = [
			'PERCENT'       => array( 'value' => 'percent' ),
			'FIXED_CART'    => array( 'value' => 'fixed_cart' ),
			'FIXED_PRODUCT' => array( 'value' => 'fixed_product' ),
		];

		register_graphql_enum_type(
			'DiscountTypeEnum',
			array(
				'description' => __( 'Coupon discount type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
