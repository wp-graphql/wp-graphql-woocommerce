<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class Discount_Type
{
	public static function register()
	{
		$values = [
			'PERCENT'       => array( 'value' => 'percent' ),
			'FIXED_CART'    => array( 'value' => 'fixed_cart' ),
			'FIXED_PRODUCT' => array( 'value' => 'fixed_product' ),
		];

		register_graphql_enum_type(
			'DiscountTypeEnum',
			array(
				'description' => __('Coupon discount type enumeration', 'wp-graphql'),
				'values'      => $values
			)
		);
	}
}
