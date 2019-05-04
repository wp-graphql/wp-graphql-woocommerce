<?php
/**
 * Mutation - restoreCartItem
 *
 * Registers mutation for restoring a removed cart item to the cart.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Cart_Restore_Item
 */
class Cart_Restore_Item {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'restoreCartItem',
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
				'resolve' => function ( $payload ) {
					return WC()->cart->get_cart_item( $payload['id'] );
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

			// Restore cart item.
			$success = $cart->restore_cart_item( $input['key'] );
			if ( false === $success ) {
				/* translators: Cart item not found message */
				throw new UserError( sprintf( __( 'Failed to restore cart item with the key: %s', 'wp-graphql-woocommerce' ), $input['key'] ) );
			}

			// Return payload.
			return array( 'id' => $input['key'] );
		};
	}
}
