<?php
/**
 * WPInterface Type - Cart_Error
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.8.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

/**
 * Class Cart_Error
 */
class Cart_Error {
	/**
	 * Registers the "CartError" interface.
	 *
	 * @return void
	 */
	public static function register_interface() {
		register_graphql_interface_type(
			'CartError',
			[
				'description' => __( 'An error that occurred when updating the cart', 'wp-graphql-woocommerce' ),
				'fields'      => self::get_fields(),
				'resolveType' => static function ( array $value ) {
					$type_registry = \WPGraphQL::get_type_registry();
					switch ( $value['type'] ) {
						case 'INVALID_CART_ITEM':
							return $type_registry->get_type( 'CartItemError' );
						case 'INVALID_COUPON':
							return $type_registry->get_type( 'CouponError' );
						case 'INVALID_SHIPPING_METHOD':
							return $type_registry->get_type( 'ShippingMethodError' );
						case 'UNKNOWN':
							return $type_registry->get_type( 'UnknownCartError' );
					}
				},
			]
		);
	}

	/**
	 * Defines CartError field. All child type must have these fields as well.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'type'    => [
				'type'        => [ 'non_null' => 'CartErrorType' ],
				'description' => __( 'Type of error', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( array $error ) {
					return ! empty( $error['type'] ) ? $error['type'] : null;
				},
			],
			'reasons' => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Reason for error', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $error ) {
					return ! empty( $error['reasons'] ) ? $error['reasons'] : [ 'Reasons for error unknown, sorry.' ];
				},
			],
		];
	}
}
