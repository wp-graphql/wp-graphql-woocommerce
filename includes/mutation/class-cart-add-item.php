<?php
/**
 * Mutation - addToCart
 *
 * Registers mutation for adding a cart item to the cart.
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
				'mutateAndGetPayload' => array( __CLASS__, 'mutate_and_get_payload' ),
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
					$item = \WC()->cart->get_cart_item( $payload['key'] );

					return $item;
				},
			),
			'cart'     => Cart_Mutation::get_cart_field( true ),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		Cart_Mutation::check_session_token();

		// Retrieve product database ID if relay ID provided.
		if ( empty( $input['productId'] ) ) {
			throw new UserError( __( 'No product ID provided', 'wp-graphql-woocommerce' ) );
		}

		if ( ! \wc_get_product( $input['productId'] ) ) {
			throw new UserError( __( 'No product found matching the ID provided', 'wp-graphql-woocommerce' ) );
		}

		// Prepare args for "add_to_cart" from input data.
		$cart_item_args = Cart_Mutation::prepare_cart_item( $input, $context, $info );

		// Add item to cart and get item key.
		try {
			$cart_item_key = \WC()->cart->add_to_cart( ...$cart_item_args );
		} catch( \Exception $e ) { // Repackage any errors.
			throw new UserError( $e->getMessage() );
		}

		// If cart item key valid return payload.
		if ( false !==  $cart_item_key ) {
			return array( 'key' => $cart_item_key );
		}

		// Process errors.
		$notices = \WC()->session->get( 'wc_notices' );
		if ( ! empty( $notices['error'] ) ) {
			$cart_error_messages = implode( ' ', array_column( $notices['error'], 'notice' ) );
			\wc_clear_notices();
			throw new UserError( $cart_error_messages );
		} else {
			throw new UserError( __( 'Failed to add cart item. Please check input.', 'wp-graphql-woocommerce' ) );
		}
	}
}
