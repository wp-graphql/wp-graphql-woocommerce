<?php
/**
 * WPEnum Type - ProductTaxonomyEnum
 *
 * @package \WPGraphQL\WooCommerce\Type\WPEnum
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
		// Get values from attributes taxonomies.
		$attribute_values = array();
		foreach ( wc_get_attribute_taxonomy_names() as $attribute ) {
			$attribute_values[ WPEnumType::get_safe_name( $attribute ) ] = array( 'value' => $attribute );
		}

		// Get values from taxonomies connected to products.
		$taxonomy_values    = array();
		$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();
		if ( ! empty( $allowed_taxonomies && is_array( $allowed_taxonomies ) ) ) {
			foreach ( $allowed_taxonomies as $taxonomy ) {
				$tax_object = get_taxonomy( $taxonomy );
				if ( in_array( $post_type, $tax_object->object_type, true ) ) {
					$taxonomy_values[ WPEnumType::get_safe_name( $tax_object->graphql_single_name ) ] = array( 'value' => $taxonomy );
				}
			}
		}

		register_graphql_enum_type(
			'ProductTaxonomyEnum',
			array(
				'description' => __( 'Product taxonomies', 'wp-graphql-woocommerce' ),
				'values'      => array_merge(
					$attribute_values,
					$taxonomy_values,
				),
			)
		);
	}
}
