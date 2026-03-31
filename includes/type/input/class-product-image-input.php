<?php
/**
 * WPInputObjectType - ProductImageInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   TBD
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
					return __( 'Product image', 'wp-graphql-woocommerce' );
				},
				'fields'      => [
					'id'      => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Image ID', 'wp-graphql-woocommerce' );
						},
					],
					'src'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Image URL', 'wp-graphql-woocommerce' );
						},
					],
					'name'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Image name', 'wp-graphql-woocommerce' );
						},
					],
					'altText' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Image alternative text', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);
	}
}
