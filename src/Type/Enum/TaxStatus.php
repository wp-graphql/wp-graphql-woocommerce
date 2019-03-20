<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class TaxStatus {
	public static function register() {
		$values = [
			'TAXABLE' => [
				'value' => 'taxable'
			],
			'SHIPPING' => [
				'value' => 'shipping'
			],
			'NONE' => [
				'value' => 'none'
			],
		];

		register_graphql_enum_type( 'TaxStatusEnum', [
			'description' => __( 'Product tax status enumeration', 'wp-graphql' ),
			'values'      => $values
		] );
	}
}