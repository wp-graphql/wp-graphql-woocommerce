<?php
/**
 * WPInputObjectType - ProductAttributeInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.1.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Attribute_Input
 */
class Product_Attribute_Input {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductAttributeInput',
			array(
				'description' => __( 'Options for ordering the connection', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'attributeName'     => array(
						'type' => array( 'non_null' => 'String' ),
					),
					'attributeValue' => array(
						'type' => 'String',
					),
				),
			)
		);
	}
}
