<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

/**
 * Class Catalog_Visibility
 *
 * This class registers GraphQL Enumeration for catalog visibility
 *
 * @package WPGraphQL\Extensions\WooCommerce\Type\Enum
 */
class Catalog_Visibility {

	/**
	 * Registers enumeration with register_graphql_enum_type
	 */
	public static function register() {
		$values = array(
			'VISIBLE' => array( 'value' => 'visible' ),
			'CATALOG' => array( 'value' => 'catalog' ),
			'SEARCH'  => array( 'value' => 'search' ),
			'HIDDEN'  => array( 'value' => 'hidden' ),
		);

		register_graphql_enum_type(
			'CatalogVisibilityEnum',
			array(
				'description' => __( 'Product catalog visibility enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values
			)
		);
	}
}
