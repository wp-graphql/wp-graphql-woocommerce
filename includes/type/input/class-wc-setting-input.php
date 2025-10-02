<?php
/**
 * WPInputObjectType - WCSettingInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class WC_Setting_Input
 */
class WC_Setting_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'WCSettingInput',
			array(
				'description' => __( 'WooCommerce setting input.', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'    => array(
						'type'        => 'String',
						'description' => __( 'A unique identifier for the setting.', 'wp-graphql-woocommerce' ),
					),
					'value' => array(
						'type'        => 'String',
						'description' => __( 'Setting value.', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
