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
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry  Instance of the WPGraphQL TypeRegistry.
	 */
	public static function register_interface( &$type_registry ) {
		register_graphql_interface_type(
			'CartError',
			array(
				'description' => __( 'An error that occurred when updating the cart', 'wp-graphql-woocommerce' ),
				'fields'      => self::get_fields(),
				'resolveType' => function( array $value ) use ( &$type_registry ) {
					switch ( $value['type'] ) {
						case 'INVALID_CART_ITEM':
							return $type_registry->get_type( 'CartItemError' );
						case 'INVALID_COUPON':
							return $type_registry->get_type( 'CouponError' );
						case 'INVALID_SHIPPING_METHOD':
							return $type_registry->get_type( 'ShippingMethodError' );
					}
				},
			)
		);
	}

	/**
	 * Defines CartError field. All child type must have these fields as well.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'type'    => array(
				'type'        => array( 'non_null' => 'CartErrorType' ),
				'description' => __( 'Type of error', 'wp-graphql-woocommerce' ),
				'resolve'     => function ( array $error ) {
					return ! empty( $error['type'] ) ? $error['type'] : null;
				},
			),
			'reasons' => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Reason for error', 'wp-graphql-woocommerce' ),
				'resolve'     => function ( $error ) {
					return ! empty( $error['reasons'] ) ? $error['reasons'] : array( 'Reasons for error unknown, sorry.' );
				},
			),
		);
	}
}
