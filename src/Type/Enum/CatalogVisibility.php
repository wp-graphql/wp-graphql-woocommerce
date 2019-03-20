<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class CatalogVisibility {
	public static function register() {
		$values = [
			'VISIBLE' => [
				'value' => 'visible'
			],
			'CATALOG' => [
				'value' => 'catalog'
			],
			'SEARCH' => [
				'value' => 'search'
      ],
      'HIDDEN' => [
				'value' => 'hidden'
			],
		];

		register_graphql_enum_type( 'CatalogVisibilityEnum', [
			'description' => __( 'Product catalog visibility enumeration', 'wp-graphql' ),
			'values'      => $values
		] );
	}
}