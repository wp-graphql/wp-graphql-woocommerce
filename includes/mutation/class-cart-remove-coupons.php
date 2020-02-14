<?php
/**
 * Mutation - removeCoupons
 *
 * Registers mutation for removing coupon(s) from cart.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Remove_Coupons
 */
class Cart_Remove_Coupons {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'removeCoupons',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);
	}

	/**
	 * Defines the mutation input field configuration.
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return array(
			'codes' => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Code of coupon being applied', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'cart' => Cart_Mutation::get_cart_field(),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input ) {
			Cart_Mutation::check_session_token();

			// Retrieve product database ID if relay ID provided.
			if ( empty( $input['codes'] ) ) {
				throw new UserError( __( 'No coupon codes provided', 'wp-graphql-woocommerce' ) );
			}

			foreach ( $input['codes'] as $code ) {

				// Check if applied.
				if ( ! \WC()->cart->has_discount( $code ) ) {
					throw new UserError( __( 'This coupon has not been applied to the cart.', 'wp-graphql-woocommerce' ) );
				}

				// Get cart item for payload.
				$success = \WC()->cart->remove_coupon( $code );
				if ( true !== $success ) {
					throw new UserError( __( 'Failed to remove coupon.', 'wp-graphql-woocommerce' ) );
				}
			}

			// Return payload.
			return array( 'cart' => \WC()->cart );
		};
	}
}
