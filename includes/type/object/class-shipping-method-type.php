<?php
/**
 * WPObject Type - Shipping_Method_Type
 *
 * Registers ShippingMethod WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Shipping_Method_Type
 */
class Shipping_Method_Type {
	/**
	 * Registers shipping method type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingMethod',
			[
				'description' => static function () {
					return __( 'A shipping method object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The globally unique identifier for the tax rate.', 'graphql-for-ecommerce' );
						},
					],
					'databaseId'  => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The ID of the shipping method in the database', 'graphql-for-ecommerce' );
						},
					],
					'title'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Shipping method title.', 'graphql-for-ecommerce' );
						},
					],
					'description' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Shipping method description.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
