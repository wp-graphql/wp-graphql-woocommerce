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
				'description' => __( 'Shipping lines data.', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'code' => [
						'type'        => 'String',
						'description' => __( 'Shipping location code.', 'wp-graphql-woocommerce' ),
					],
					'type' => [
						'type'        => 'ShippingLocationTypeEnum',
						'description' => __( 'Shipping location type.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
