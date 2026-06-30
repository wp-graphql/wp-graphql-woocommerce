<?php
/**
 * WPObject Type - Shipping_Location_Type
 *
 * Registers ShippingLocation WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Shipping_Location_Type
 */
class Shipping_Location_Type {
	/**
	 * Registers shipping location type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingLocation',
			[
				'eagerlyLoadType' => true,
				'description'     => static function () {
					return __( 'A Shipping zone object', 'graphql-for-ecommerce' );
				},
				'fields'          => [
					'code' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'The globally unique identifier for the tax rate.', 'graphql-for-ecommerce' );
						},
					],
					'type' => [
						'type'        => 'ShippingLocationTypeEnum',
						'description' => static function () {
							return __( 'Shipping zone location name.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
