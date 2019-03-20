<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class StockStatus {
	public static function register() {
		$values = [
			'IN_STOCK' => [
				'value' => 'instock'
			],
			'OUT_OF_STOCK' => [
				'value' => 'outofstock'
			],
			'ON_BACKORDER' => [
				'value' => 'onbackorder'
			],
		];

		register_graphql_enum_type( 'StockStatusEnum', [
			'description' => __( 'Product stock status enumeration', 'wp-graphql' ),
			'values'      => $values
		] );
	}
}