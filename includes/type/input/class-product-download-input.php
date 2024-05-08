<?php
/**
 * WPInputObjectType - ProductDownloadInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   TBD
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
				'description' => __( 'Product download', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'id'          => [
                        'type'        => 'Int',
                        'description' => __( 'File ID', 'wp-graphql-woocommerce' ),
                    ],
                    'name'        => [
                        'type'        => [ 'non_null' => 'String' ],
                        'description' => __( 'File name', 'wp-graphql-woocommerce' ),
                    ],
                    'file'        => [
                        'type'        => [ 'non_null' => 'String' ],
                        'description' => __( 'File URL', 'wp-graphql-woocommerce' ),
                    ],
				],
			]
		);
	}
}
