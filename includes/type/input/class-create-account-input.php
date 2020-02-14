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
	 */
	public static function register() {
		register_graphql_input_type(
			'CreateAccountInput',
			array(
				'description' => __( 'Customer account credentials', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'username' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Customer username', 'wp-graphql-woocommerce' ),
					),
					'password' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Customer password', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
