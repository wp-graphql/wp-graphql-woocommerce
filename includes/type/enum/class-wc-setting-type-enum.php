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
			array(
				'description' => __( 'Type of WC setting.', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'TEXT'         => array( 'value' => 'text' ),
					'EMAIL'        => array( 'value' => 'email' ),
					'NUMBER'       => array( 'value' => 'number' ),
					'COLOR'        => array( 'value' => 'color' ),
					'PASSWORD'     => array( 'value' => 'password' ),
					'TEXTAREA'     => array( 'value' => 'textarea' ),
					'SELECT'       => array( 'value' => 'select' ),
					'MULTI_SELECT' => array( 'value' => 'multi_select' ),
					'RADIO'        => array( 'value' => 'radio' ),
					'IMAGE_WIDTH'  => array( 'value' => 'image_width' ),
					'CHECKBOX'     => array( 'value' => 'checkbox' ),
				),
			)
		);
	}
}
