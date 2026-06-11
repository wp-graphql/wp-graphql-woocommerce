<?php
/**
 * WPInputObjectType - ShippingLocationInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Shipping_Location_Input
 */
class Shipping_Location_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ShippingLocationInput',
			[
				'description' => static function () {
					return __( 'Shipping lines data.', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'code' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Shipping location code.', 'graphql-for-ecommerce' );
						},
					],
					'type' => [
						'type'        => 'ShippingLocationTypeEnum',
						'description' => static function () {
							return __( 'Shipping location type.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
