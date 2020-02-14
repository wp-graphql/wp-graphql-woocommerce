<?php
/**
 * WPInputObjectType - ProductTaxonomyFilterRelationInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Taxonomy_Filter_Relation_Input
 */
class Product_Taxonomy_Filter_Relation_Input {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductTaxonomyFilterRelationInput',
			array(
				'description' => __( 'Product taxonomy filter type', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'or'  => array(
						'type' => array( 'list_of' => 'ProductTaxonomyFilterInput' ),
					),
					'and' => array(
						'type' => array( 'list_of' => 'ProductTaxonomyFilterInput' ),
					),
				),
			)
		);
	}
}
