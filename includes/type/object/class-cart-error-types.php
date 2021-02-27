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
	 */
	public static function register() {
		register_graphql_object_type(
			'CartItemError',
			array(
				'description' => __( 'Error that occurred when adding an item to the cart.', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'CartError' ),
				'fields'      => array(
					'productId'   => array(
						'type'        => array( 'non_null' => 'Int' ),
						'description' => __( 'Cart item product database ID or global ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['productId'] ) ? $error['productId'] : null;
						},
					),
					'quantity'    => array(
						'type'        => 'Int',
						'description' => __( 'Cart item quantity', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['quantity'] ) ? $error['quantity'] : null;
						},
					),
					'variationId' => array(
						'type'        => 'Int',
						'description' => __( 'Cart item product variation database ID or global ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['variationId'] ) ? $error['variationId'] : null;
						},
					),
					'variation'   => array(
						'type'        => array( 'list_of' => 'ProductAttributeOutput' ),
						'description' => __( 'Cart item product variation attributes', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['variation'] ) ? $error['variation'] : null;
						},
					),
					'extraData'   => array(
						'type'        => 'String',
						'description' => __( 'JSON string representation of extra cart item data', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['extraData'] ) ? $error['extraData'] : null;
						},
					),
				),
			)
		);

		register_graphql_object_type(
			'CouponError',
			array(
				'description' => __( 'Error that occurred when applying a coupon to the cart.', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'CartError' ),
				'fields'      => array(
					'code' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Coupon code of the coupon the failed to be applied', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['code'] ) ? $error['code'] : null;
						},
					),
				),
			)
		);

		register_graphql_object_type(
			'ShippingMethodError',
			array(
				'description' => __( 'Error that occurred when setting the chosen shipping method for the eventually order.', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'CartError' ),
				'fields'      => array(
					'package'      => array(
						'type'        => array( 'non_null' => 'Integer' ),
						'description' => __( 'Index of package for desired shipping method', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return isset( $error['package'] ) && is_int( $error['package'] ) ? $error['package'] : null;
						},
					),
					'chosenMethod' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'ID of chosen shipping rate', 'wp-graphql-woocommerce' ),
						'resolve'     => function( array $error ) {
							return ! empty( $error['chosen_method'] ) ? $error['chosen_method'] : null;
						},
					),
				),
			)
		);
	}
}
