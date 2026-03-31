<?php
/**
 * WPInputObjectType - ProductDimensionsInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   TBD
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
					return __( 'Product dimensions', 'wp-graphql-woocommerce' );
				},
				'fields'      => [
					'length' => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Length of the product', 'wp-graphql-woocommerce' );
				},
					],
					'width'  => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Width of the product', 'wp-graphql-woocommerce' );
				},
					],
					'height' => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Height of the product', 'wp-graphql-woocommerce' );
				},
					],
				],
			]
		);
	}
}
