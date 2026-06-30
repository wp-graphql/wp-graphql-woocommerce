<?php
/**
 * WPInputObjectType - ProductDimensionsInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Dimensions_Input
 */
class Product_Dimensions_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductDimensionsInput',
			[
				'description' => static function () {
					return __( 'Product dimensions', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'length' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Length of the product', 'graphql-for-ecommerce' );
						},
					],
					'width'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Width of the product', 'graphql-for-ecommerce' );
						},
					],
					'height' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Height of the product', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
