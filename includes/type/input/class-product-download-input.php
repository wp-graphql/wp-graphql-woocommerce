<?php
/**
 * WPInputObjectType - ProductDownloadInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Download_Input
 */
class Product_Download_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductDownloadInput',
			[
				'description' => static function () {
					return __( 'Product download', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'   => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'File ID', 'graphql-for-ecommerce' );
						},
					],
					'name' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'File name', 'graphql-for-ecommerce' );
						},
					],
					'file' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'File URL', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
