<?php
/**
 * Mutation - removeItemsFromCart
 *
 * Registers mutation for removing cart item(s) from the cart.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Remove_Items
 */
class Cart_Remove_Items {

	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'removeItemsFromCart',
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
			'keys' => [
				'type'        => [ 'list_of' => 'ID' ],
				'description' => __( 'Item keys of the items being removed', 'wp-graphql-woocommerce' ),
			],
			'all'  => [
				'type'        => 'Boolean',
				'description' => __( 'Remove all cart items', 'wp-graphql-woocommerce' ),
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
			'cartItems' => [
				'type'    => [ 'list_of' => 'CartItem' ],
				'resolve' => function ( $payload ) {
					return $payload['items'];
				},
			],
			'cart'      => Cart_Mutation::get_cart_field( true ),
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			Cart_Mutation::check_session_token();

			if ( \WC()->cart->is_empty() ) {
				throw new UserError( __( 'No items in cart to remove.', 'wp-graphql-woocommerce' ) );
			}

			if ( empty( $input['keys'] ) && empty( $input['all'] ) ) {
				throw new UserError( __( 'No cart item keys provided', 'wp-graphql-woocommerce' ) );
			}

			$cart_items = Cart_Mutation::retrieve_cart_items( $input, $context, $info, 'remove' );
			foreach ( $cart_items as $item ) {
				$success = \WC()->cart->remove_cart_item( $item['key'] );
				if ( false === $success ) {
					/* translators: Cart item removal failure message */
					throw new UserError( sprintf( __( 'Failed to remove item %s from cart.', 'wp-graphql-woocommerce' ), $item['key'] ) );
				}
			}

			// Return payload.
			return [ 'items' => $cart_items ];
		};
	}
}
