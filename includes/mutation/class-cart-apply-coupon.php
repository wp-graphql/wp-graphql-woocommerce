<?php
/**
 * Mutation - applyCoupon
 *
 * Registers mutation for applying a coupon.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Apply_Coupon
 */
class Cart_Apply_Coupon {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'applyCoupon',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'code' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'Code of coupon being applied', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'applied' => [
				'type'    => 'AppliedCoupon',
				'resolve' => static function ( $payload ) {
					return $payload['code'];
				},
			],
			'cart'    => Cart_Mutation::get_cart_field( true ),
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input ) {
			Cart_Mutation::check_session_token();

			$reason = '';
			// If validate and successful applied to cart, return payload.
			if ( Cart_Mutation::validate_coupon( $input['code'], $reason ) && \WC()->cart->apply_coupon( $input['code'] ) ) {
				return [ 'code' => $input['code'] ];
			}

			// If any session error notices, capture them.
			$notices = \WC()->session->get( 'wc_notices' );
			if ( ! empty( $notices['error'] ) ) {
				$reason = implode( ' ', array_column( $notices['error'], 'notice' ) );
				\wc_clear_notices();
			}

			// Throw any capture errors.
			if ( ! empty( $reason ) ) {
				throw new UserError( $reason );
			}

			// Throw for unknown failure.
			throw new UserError( __( 'Failed to apply coupon. Check for an individual-use coupon on cart.', 'wp-graphql-woocommerce' ) );
		};
	}
}
