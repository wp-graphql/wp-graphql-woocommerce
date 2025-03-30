<?php
/**
 * WPEnum Type - CatalogVisibilityEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Catalog_Visibility
 */
class Catalog_Visibility {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$values = [
			'VISIBLE' => [ 'value' => 'visible' ],
			'CATALOG' => [ 'value' => 'catalog' ],
			'SEARCH'  => [ 'value' => 'search' ],
			'HIDDEN'  => [ 'value' => 'hidden' ],
		];

		register_graphql_enum_type(
			'CatalogVisibilityEnum',
			[
				'description' => __( 'Product catalog visibility enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
