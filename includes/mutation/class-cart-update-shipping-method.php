<?php
/**
 * Mutation - updateShippingMethod
 *
 * Registers mutation for update the shipping method for the order.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.3.2
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Update_Shipping_Method
 */
class Cart_Update_Shipping_Method {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateShippingMethod',
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
			'shippingMethods' => [
				'type' => [ 'list_of' => 'String' ],
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
			'cart' => Cart_Mutation::get_cart_field( true ),
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

			if ( empty( $input['shippingMethods'] ) ) {
				throw new UserError( __( 'No shipping method provided', 'wp-graphql-woocommerce' ) );
			}

			$chosen_shipping_methods = Cart_Mutation::prepare_shipping_methods( $input['shippingMethods'] );

			// Set updated shipping methods in session.
			\WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

			// Recalculate totals.
			\WC()->cart->calculate_totals();

			return [];
		};
	}
}
