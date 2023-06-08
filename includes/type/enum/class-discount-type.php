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
	 *
	 * @return void
	 */
	public static function register() {
		$values = [];
		foreach ( \wc_get_coupon_types() as $value => $description ) {
			$values[ strtoupper( $value ) ] = compact( 'value', 'description' );
		}

		register_graphql_enum_type(
			'DiscountTypeEnum',
			[
				'description' => __( 'Coupon discount type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
