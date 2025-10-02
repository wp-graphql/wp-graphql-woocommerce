<?php
/**
 * WPInputObjectType - ProductAttributeFilterInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.18.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Attribute_Filter_Input
 */
class Product_Attribute_Filter_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductAttributeFilterInput',
			array(
				'description' => __( 'Product filter', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'taxonomy' => array(
						'type'        => array( 'non_null' => 'ProductAttributeEnum' ),
						'description' => __( 'Which field to select taxonomy term by.', 'wp-graphql-woocommerce' ),
					),
					'terms'    => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'A list of term slugs', 'wp-graphql-woocommerce' ),
					),
					'ids'      => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'A list of term ids', 'wp-graphql-woocommerce' ),
					),
					'operator' => array(
						'type'        => 'AttributeOperatorEnum',
						'description' => __( 'Filter operation type', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
