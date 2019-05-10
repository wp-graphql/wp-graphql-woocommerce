<?php
/**
 * Mutation - removeCoupon
 *
 * Registers mutation for removing a coupon from cart.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Cart_Remove_Coupon
 */
class Cart_Remove_Coupon {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'removeCoupon',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		$input_fields = array(
			'code' => array(
				'type'        => array( 'non_null' => 'String' ),
				'description' => __( 'Code of coupon being applied', 'wp-graphql-woocommerce' ),
			),
		);

		return $input_fields;
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'cart' => array(
				'type'    => 'Cart',
				'resolve' => function ( $payload ) {
					return $payload['cart'];
				},
			),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			// Retrieve product database ID if relay ID provided.
			if ( empty( $input['code'] ) ) {
				throw new UserError( __( 'No coupon code provided', 'wp-graphql-woocommerce' ) );
			}

			// Get the coupon.
			$the_coupon = new \WC_Coupon( $input['code'] );

			// Check if applied.
			if ( ! \WC()->cart->has_discount( $input['code'] ) ) {
				throw new UserError( __( 'This coupon has not been applied to the cart.', 'wp-graphql-woocommerce' ) );
			}

			// Get cart item for payload.
			$success = \WC()->cart->remove_coupon( $input['code'] );
			if ( true !== $success ) {
				throw new UserError( __( 'Failed to remove coupon.', 'wp-graphql-woocommerce' ) );
			}

			// Return payload.
			return array( 'cart' => \WC()->cart );
		};
	}
}
