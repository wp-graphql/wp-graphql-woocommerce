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
				'values'      => $values,
			)
		);
	}
}
