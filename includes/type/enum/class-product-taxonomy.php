<?php
/**
 * WPEnum Type - ProductTaxonomyEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

use WPGraphQL\WooCommerce\Utils\Label;

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
				$safe_name = Label::get_safe_enum_name( $taxonomy );
				if ( null === $safe_name ) {
					continue;
				}
				$taxonomy_values[ $safe_name ] = [ 'value' => $taxonomy ];
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
