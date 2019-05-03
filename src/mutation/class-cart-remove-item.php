<?php
/**
 * Mutation - removeItemFromCart
 *
 * Registers mutation for removing a cart item from the cart.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Cart_Remove_Item
 */
class Cart_Remove_Item {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'removeItemFromCart',
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
			'key' => array(
				'type'        => array( 'non_null' => 'ID' ),
				'description' => __( 'Cart item key of the item being removed', 'wp-graphql-woocommerce' ),
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
			'cartItem' => array(
				'type'    => 'CartItem',
				'resolve' => function ( $item ) {
					return $item;
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
			if ( empty( $input['key'] ) ) {
				throw new UserError( __( 'No cart item key provided', 'wp-graphql-woocommerce' ) );
			}

			// Get WC_Cart instance.
			$cart = WC()->cart;

			$cart_item = $cart->get_cart_item( $input['key'] );
			if ( empty( $cart_item ) ) {
				/* translators: Cart item not found message */
				throw new UserError( sprintf( __( 'No cart item found with the key: %s', 'wp-graphql-woocommerce' ), $input['key'] ) );
			}

			// Add item to cart and get item key.
			$cart->remove_cart_item( $input['key'] );

			// Return payload.
			return $cart_item;
		};
	}
}
