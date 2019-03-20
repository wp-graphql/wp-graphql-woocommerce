<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class Backorders {
	public static function register() {
		$values = [
			'NO' => [
				'value' => 'no'
			],
			'NOTIFY' => [
				'value' => 'notify'
			],
			'YES' => [
				'value' => 'yes'
			],
		];

		register_graphql_enum_type( 'BackorderEnum', [
			'description' => __( 'Product backorder enumeration', 'wp-graphql' ),
			'values'      => $values
		] );
	}
}
