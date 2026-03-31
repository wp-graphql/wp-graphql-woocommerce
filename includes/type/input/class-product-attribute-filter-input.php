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
			[
				'description' => static function () {
					return __( 'Product filter', 'wp-graphql-woocommerce' );
				},
				'fields'      => [
					'taxonomy' => [
						'type'        => [ 'non_null' => 'ProductAttributeEnum' ],
						'description' => static function () {
							return __( 'Which field to select taxonomy term by.', 'wp-graphql-woocommerce' );
						},
					],
					'terms'    => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'A list of term slugs', 'wp-graphql-woocommerce' );
						},
					],
					'ids'      => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'A list of term ids', 'wp-graphql-woocommerce' );
						},
					],
					'operator' => [
						'type'        => 'AttributeOperatorEnum',
						'description' => static function () {
							return __( 'Filter operation type', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);
	}
}
