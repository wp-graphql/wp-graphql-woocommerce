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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductAttributeInput',
			[
				'description' => __( 'Options for ordering the connection', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'attributeName'  => [
						'type' => [ 'non_null' => 'String' ],
					],
					'attributeValue' => [
						'type' => 'String',
					],
				],
			]
		);
	}
}
