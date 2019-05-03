<?php
/**
 * Defines helper functions for executing mutations related to the cart.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Mutation;

/**
 * Class - Cart_Mutation
 */
class Cart_Mutation {
	/**
	 * Return array of data to be when defining a cart item
	 *
	 * @param array       $input   input data describing cart item.
	 * @param AppContext  $context AppContext instance.
	 * @param ResolveInfo $info    query info.
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
}
