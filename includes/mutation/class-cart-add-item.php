<?php
/**
 * Mutation - addToCart
 *
 * Registers mutation for adding a cart item to the cart.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Add_Item
 */
class Cart_Add_Item {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'addToCart',
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
			'productId'   => array(
				'type'        => array( 'non_null' => 'Int' ),
				'description' => __( 'Cart item product database ID or global ID', 'wp-graphql-woocommerce' ),
			),
			'quantity'    => array(
				'type'        => 'Int',
				'description' => __( 'Cart item quantity', 'wp-graphql-woocommerce' ),
			),
			'variationId' => array(
				'type'        => 'Int',
				'description' => __( 'Cart item product variation database ID or global ID', 'wp-graphql-woocommerce' ),
			),
			'variation'   => array(
				'type'        => array( 'list_of' => 'ProductAttributeInput' ),
				'description' => __( 'Cart item product variation attributes', 'wp-graphql-woocommerce' ),
			),
			'extraData'   => array(
				'type'        => 'String',
				'description' => __( 'JSON string representation of extra cart item data', 'wp-graphql-woocommerce' ),
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
					$cart = WC()->cart;
					$item = $cart->get_cart_item( $payload['key'] );

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
			if ( empty( $input['productId'] ) ) {
				throw new UserError( __( 'No product ID provided', 'wp-graphql-woocommerce' ) );
			}

			// Prepare args for "add_to_cart" from input data.
			$cart_item_args = Cart_Mutation::prepare_cart_item( $input, $context, $info );

			// Add item to cart and get item key.
			$cart_item_key = \WC()->cart->add_to_cart( ...$cart_item_args );

			// Return payload.
			return array( 'key' => $cart_item_key );
		};
	}
}
