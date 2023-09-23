<?php
/**
 * WPEnum Type - ProductAttributeEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.18.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class Product_Attribute_Enum
 */
class Product_Attribute_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		// Get values from product attributes.
		$taxonomy_values = [];
		$taxonomies      = wc_get_attribute_taxonomy_names();

		foreach ( $taxonomies as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );

			if ( false !== $tax_object && in_array( 'product', $tax_object->object_type, true ) ) {
				$taxonomy_values[ WPEnumType::get_safe_name( $taxonomy ) ] = [ 'value' => $taxonomy ];
			}
		}

		register_graphql_enum_type(
			'ProductAttributeEnum',
			[
				'description' => __( 'Product attribute taxonomies', 'wp-graphql-woocommerce' ),
				'values'      => $taxonomy_values,
			]
		);
	}
}
