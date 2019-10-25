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

		register_graphql_enum_type(
			'ProductTaxonomyEnum',
			array(
				'description' => __( 'Product taxonomies', 'wp-graphql-woocommerce' ),
				'values'      => array_merge(
					array(
						'TYPE'     => array( 'value' => 'product_type' ),
						'CATEGORY' => array( 'value' => 'product_cat' ),
						'TAG'      => array( 'value' => 'product_tag' ),
						'BRAND'    => array( 'value' => 'product_brand' ),
					),
					$attribute_values
				),
			)
		);
	}
}
