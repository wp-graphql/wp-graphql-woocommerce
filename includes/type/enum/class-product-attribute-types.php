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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'ProductAttributeTypesEnum',
			[
				'description' => static function () {
					return __( 'Product attribute type enumeration', 'graphql-for-ecommerce' );
				},
				'values'      => [
					'LOCAL'  => [
						'value'       => 'local',
						'description' => static function () {
							return __( 'A local product attribute', 'graphql-for-ecommerce' );
						},
					],
					'GLOBAL' => [
						'value'       => 'global',
						'description' => static function () {
							return __( 'A global product attribute', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
