<?php
/**
 * WPEnum Type - WCSettingTypeEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class WC_Setting_Type_Enum
 */
class WC_Setting_Type_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'WCSettingTypeEnum',
			[
				'description' => __( 'Type of WC setting.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'TEXT'         => [ 'value' => 'text' ],
					'EMAIL'        => [ 'value' => 'email' ],
					'NUMBER'       => [ 'value' => 'number' ],
					'COLOR'        => [ 'value' => 'color' ],
					'PASSWORD'     => [ 'value' => 'password' ],
					'TEXTAREA'     => [ 'value' => 'textarea' ],
					'SELECT'       => [ 'value' => 'select' ],
					'MULTI_SELECT' => [ 'value' => 'multi_select' ],
					'RADIO'        => [ 'value' => 'radio' ],
					'IMAGE_WIDTH'  => [ 'value' => 'image_width' ],
					'CHECKBOX'     => [ 'value' => 'checkbox' ],
				],
			]
		);
	}
}
