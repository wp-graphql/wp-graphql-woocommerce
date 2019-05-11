<?php
/**
 * Defines helper functions for executing mutations related to the cart.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;

/**
 * Class - Cart_Mutation
 */
class Cart_Mutation {
	/**
	 * Returns a cart item.
	 *
	 * @param array       $input   Input data describing cart item.
	 * @param AppContext  $context AppContext instance.
	 * @param ResolveInfo $info    Query info.
	 *
	 * @return array
	 */
	public static function prepare_cart_item( $input, $context, $info ) {
		$cart_item_args = array(
			$input['productId'],
			! empty( $input['quantity'] ) ? $input['quantity'] : 1,
			! empty( $input['variationId'] ) ? $input['variationId'] : 0,
			! empty( $input['variation'] ) ? $input['variation'] : array(),
			! empty( $input['extraData'] ) ? json_decode( $input['extraData'], true ) : array(),
		);

		return apply_filters( 'woocommerce_new_cart_item_data', $cart_item_args, $input, $context, $info );
	}

	/**
	 * Returns an array of cart items.
	 *
	 * @param array       $input    Input data describing cart items.
	 * @param AppContext  $context  AppContext instance.
	 * @param ResolveInfo $info     Query info.
	 * @param string      $mutation Mutation type.
	 *
	 * @return array
	 * @throws UserError Cart item not found message.
	 */
	public static function retrieve_cart_items( $input, $context, $info, $mutation = '' ) {
		if ( ! empty( $input['all'] ) && $input['all'] ) {
			$items = array_values( \WC()->cart->get_cart() );
		}

		if ( ! empty( $input['keys'] ) && ! isset( $items ) ) {
			$items = array();
			foreach ( $input['keys'] as $key ) {
				$item = \WC()->cart->get_cart_item( $key );
				if ( empty( $item ) ) {
					/* translators: Cart item not found message */
					throw new UserError( sprintf( __( 'No cart item found with the key: %s', 'wp-graphql-woocommerce' ), $key ) );
				}
				$items[] = $item;
			}
		}

		return apply_filters( 'retrieve_cart_items', $items, $input, $context, $info, $mutation );
	}

	/**
	 * Return array of data to be when defining a cart fee.
	 *
	 * @param array       $input   input data describing cart item.
	 * @param AppContext  $context AppContext instance.
	 * @param ResolveInfo $info    query info.
	 *
	 * @return array
	 */
	public static function prepare_cart_fee( $input, $context, $info ) {
		$cart_item_args = array(
			$input['name'],
			$input['amount'],
			! empty( $input['taxable'] ) ? $input['taxable'] : false,
			! empty( $input['taxClass'] ) ? $input['taxClass'] : '',
		);

		return apply_filters( 'woocommerce_new_cart_fee_data', $cart_item_args, $input, $context, $info );
	}
}
