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
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateShippingMethod',
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
		return array(
			'shippingMethods' => array(
				'type' => array( 'list_of' => 'String' ),
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
			'cart' => Cart_Mutation::get_cart_field( true ),
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

			if ( empty( $input['shippingMethods'] ) ) {
				throw new UserError( __( 'No shipping method provided', 'wp-graphql-woocommerce' ) );
			}

			$posted_shipping_methods = $input['shippingMethods'];

			// Get current shipping methods.
			$chosen_shipping_methods = \WC()->session->get( 'chosen_shipping_methods' );

			// Update current shipping methods.
			foreach ( $posted_shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}

			// Set updated shipping methods in session.
			\WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

			// Recalculate totals.
			\WC()->cart->calculate_totals();

			return array();
		};
	}
}
