<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class Stock_Status
{
	public static function register()
	{
		$values = [
			'IN_STOCK'     => array( 'value' => 'instock' ),
			'OUT_OF_STOCK' => array( 'value' => 'outofstock' ),
			'ON_BACKORDER' => array( 'value' => 'onbackorder' ),
		];

		register_graphql_enum_type(
			'StockStatusEnum',
			array(
				'description' => __('Product stock status enumeration', 'wp-graphql'),
				'values'      => $values
			)
		);
	}
}
