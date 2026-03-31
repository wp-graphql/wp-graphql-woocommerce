<?php
/**
 * WPObject Types - CartItemError, CouponError, ShippingMethodError
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.8.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Cart_Error_Types
 */
class Cart_Error_Types {
	/**
	 * Registers types to the GraphQL schema.
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'CartItemError',
			[
				'description' => static function () {
					return __( 'Error that occurred when adding an item to the cart.', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'CartError' ],
				'fields'      => [
					'productId'   => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => static function () {
							return __( 'Cart item product database ID or global ID', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['productId'] ) ? $error['productId'] : null;
						},
					],
					'quantity'    => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Cart item quantity', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['quantity'] ) ? $error['quantity'] : null;
						},
					],
					'variationId' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Cart item product variation database ID or global ID', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['variationId'] ) ? $error['variationId'] : null;
						},
					],
					'variation'   => [
						'type'        => [ 'list_of' => 'ProductAttributeOutput' ],
						'description' => static function () {
							return __( 'Cart item product variation attributes', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['variation'] ) ? $error['variation'] : null;
						},
					],
					'extraData'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'JSON string representation of extra cart item data', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['extraData'] ) ? $error['extraData'] : null;
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'CouponError',
			[
				'description' => static function () {
					return __( 'Error that occurred when applying a coupon to the cart.', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'CartError' ],
				'fields'      => [
					'code' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Coupon code of the coupon the failed to be applied', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['code'] ) ? $error['code'] : null;
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'ShippingMethodError',
			[
				'description' => static function () {
					return __( 'Error that occurred when setting the chosen shipping method for the eventually order.', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'CartError' ],
				'fields'      => [
					'package'      => [
						'type'        => [ 'non_null' => 'Integer' ],
						'description' => static function () {
							return __( 'Index of package for desired shipping method', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return isset( $error['package'] ) && is_int( $error['package'] ) ? $error['package'] : null;
						},
					],
					'chosenMethod' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'ID of chosen shipping rate', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( array $error ) {
							return ! empty( $error['chosen_method'] ) ? $error['chosen_method'] : null;
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'UnknownCartError',
			[
				'description' => static function () {
					return __( 'Error that occurred with no recognizable reason.', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'CartError' ],
				'fields'      => [],
			]
		);
	}
}
