<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class DiscountType {
	public static function register() {
		$values = [
			'PERCENT' => [
				'value' => 'percent'
			],
			'FIXED_CART' => [
				'value' => 'fixed_cart'
			],
			'FIXED_PRODUCT' => [
				'value' => 'fixed_product'
      ],
		];

		register_graphql_enum_type( 'DiscountTypeEnum', [
			'description' => __( 'Coupon discount type enumeration', 'wp-graphql' ),
			'values'      => $values
		] );
	}
}