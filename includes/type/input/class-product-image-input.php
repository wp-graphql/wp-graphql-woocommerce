<?php
/**
 * WPInputObjectType - ProductImageInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Image_Input
 */
class Product_Image_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductImageInput',
			[
				'description' => static function () {
					return __( 'Product image', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'      => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Image ID', 'graphql-for-ecommerce' );
						},
					],
					'src'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Image URL', 'graphql-for-ecommerce' );
						},
					],
					'name'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Image name', 'graphql-for-ecommerce' );
						},
					],
					'altText' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Image alternative text', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
