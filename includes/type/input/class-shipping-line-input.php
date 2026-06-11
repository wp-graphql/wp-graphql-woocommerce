<?php
/**
 * WPInputObjectType - ShippingLineInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Shipping_Line_Input
 */
class Shipping_Line_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ShippingLineInput',
			[
				'description' => static function () {
					return __( 'Shipping lines data.', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'          => [
						'type'        => 'ID',
						'description' => static function () {
							return __( 'Shipping Line ID', 'graphql-for-ecommerce' );
						},
					],
					'methodTitle' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Shipping method name.', 'graphql-for-ecommerce' );
						},
					],
					'methodId'    => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Shipping method ID.', 'graphql-for-ecommerce' );
						},
					],
					'instanceId'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Shipping instance ID.', 'graphql-for-ecommerce' );
						},
					],
					'total'       => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Line total (after discounts).', 'graphql-for-ecommerce' );
						},
					],
					'metaData'    => [
						'type'        => [ 'list_of' => 'MetaDataInput' ],
						'description' => static function () {
							return __( 'Meta data.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
