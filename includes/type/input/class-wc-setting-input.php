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
			[
				'description' => static function () {
					return __( 'WooCommerce setting input.', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A unique identifier for the setting.', 'graphql-for-ecommerce' );
						},
					],
					'value' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Setting value.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
