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
	 *
	 * @return void
	 */
	public static function register() {
		// Get values from taxonomies connected to products.
		$taxonomy_values    = [];
		$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();

		foreach ( $allowed_taxonomies as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );

			if ( false !== $tax_object && in_array( 'product', $tax_object->object_type, true ) ) {
				$taxonomy_values[ WPEnumType::get_safe_name( $tax_object->graphql_single_name ) ] = [ 'value' => $taxonomy ];
			}
		}

		register_graphql_enum_type(
			'ProductTaxonomyEnum',
			[
				'description' => __( 'Product taxonomies', 'wp-graphql-woocommerce' ),
				'values'      => $taxonomy_values,
			]
		);
	}
}
