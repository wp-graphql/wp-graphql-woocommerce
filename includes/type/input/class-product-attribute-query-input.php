<?php
/**
 * WPInputObjectType - ProductAttributeQueryInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Attribute_Query_Input
 */
class Product_Attribute_Query_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductAttributeQueryInput',
			[
				'description' => __( 'Product filter', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'queries'  => [
						'type'        => [ 'list_of' => 'ProductAttributeFilterInput' ],
						'description' => __( 'Limit result set to products with selected global attributes.', 'wp-graphql-woocommerce' ),
					],
					'relation' => [
						'type'        => 'AttributeOperatorEnum',
						'description' => __( 'The logical relationship between attributes when filtering across multiple at once.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
