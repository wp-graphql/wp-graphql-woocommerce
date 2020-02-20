<?php
/**
 * WPEnum Type - ProductTaxonomyEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class Product_Taxonomy
 */
class Product_Taxonomy {
	/**
	 * Registers type
	 */
	public static function register() {
		// Get values from taxonomies connected to products.
		$taxonomy_values    = array();
		$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();
		if ( ! empty( $allowed_taxonomies && is_array( $allowed_taxonomies ) ) ) {
			foreach ( $allowed_taxonomies as $taxonomy ) {
				$tax_object = get_taxonomy( $taxonomy );
				if ( in_array( 'product', $tax_object->object_type, true ) ) {
					$taxonomy_values[ WPEnumType::get_safe_name( $tax_object->graphql_single_name ) ] = array( 'value' => $taxonomy );
				}
			}
		}

		register_graphql_enum_type(
			'ProductTaxonomyEnum',
			array(
				'description' => __( 'Product taxonomies', 'wp-graphql-woocommerce' ),
				'values'      => $taxonomy_values,
			)
		);
	}
}
