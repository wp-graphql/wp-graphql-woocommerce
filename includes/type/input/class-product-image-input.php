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
				'description' => __( 'Product image', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'id'      => [
						'type'        => 'Int',
						'description' => __( 'Image ID', 'wp-graphql-woocommerce' ),
					],
					'src'     => [
						'type'        => 'String',
						'description' => __( 'Image URL', 'wp-graphql-woocommerce' ),
					],
					'name'    => [
						'type'        => 'String',
						'description' => __( 'Image name', 'wp-graphql-woocommerce' ),
					],
					'altText' => [
						'type'        => 'String',
						'description' => __( 'Image alternative text', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
