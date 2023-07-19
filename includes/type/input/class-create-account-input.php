<?php
/**
 * WPInputObjectType - CreateAccountInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Create_Account_Input
 */
class Create_Account_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'CreateAccountInput',
			[
				'description' => __( 'Customer account credentials', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'username' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Customer username', 'wp-graphql-woocommerce' ),
					],
					'password' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Customer password', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
