<?php
/**
 * Mutation - restoreCartItems
 *
 * Registers mutation for restoring removed cart item(s) to the cart.
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
 * Class - Cart_Restore_Items
 */
class Cart_Restore_Items {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'restoreCartItems',
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
				'description' => __( 'Cart item key of the item being removed', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return Cart_Remove_Items::get_output_fields();
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			Cart_Mutation::check_session_token();

			if ( empty( $input['keys'] ) ) {
				throw new UserError( __( 'No cart item keys provided', 'wp-graphql-woocommerce' ) );
			}

			// Restore cart items.
			foreach ( $input['keys'] as $key ) {
				$success = \WC()->cart->restore_cart_item( $key );
				if ( false === $success ) {
					/* translators: Cart item not found message */
					throw new UserError( sprintf( __( 'Failed to restore cart item with the key: %s', 'wp-graphql-woocommerce' ), $key ) );
				}
			}

			$cart_items = Cart_Mutation::retrieve_cart_items( $input, $context, $info, 'restore' );

			// Return payload.
			return [ 'items' => $cart_items ];
		};
	}
}
