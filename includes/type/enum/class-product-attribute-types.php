<?php
/**
 * WPEnum Type - ProductAttributeTypesEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Product_Attribute_Types
 */
class Product_Attribute_Types {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_enum_type(
			'ProductAttributeTypesEnum',
			array(
				'description' => __( 'Product attribute type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'LOCAL'  => array(
						'value'       => 'local',
						'description' => __( 'A local product attribute', 'wp-graphql-woocommerce' ),
					),
					'GLOBAL' => array(
						'value'       => 'global',
						'description' => __( 'A global product attribute', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
