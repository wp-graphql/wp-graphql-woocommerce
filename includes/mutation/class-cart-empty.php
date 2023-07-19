<?php
/**
 * Mutation - emptyCart
 *
 * Registers mutation for empty cart of all contents including coupons and fees.
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
 * Class - Cart_Empty
 */
class Cart_Empty {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'emptyCart',
			[
				'inputFields'         => [
					'clearPersistentCart' => [ 'type' => 'Boolean' ],
				],
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'deletedCart' => Cart_Mutation::get_cart_field(),
			'cart'        => [
				'type'    => 'Cart',
				'resolve' => static function () {
					return \WC()->cart;
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			Cart_Mutation::check_session_token();

			// Get/Clone WC_Cart instance.
			$cloned_cart = clone \WC()->cart;

			if ( $cloned_cart->is_empty() ) {
				throw new UserError( __( 'Cart is empty', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Action fired before cart was cleared/emptied.
			 *
			 * @param object      $cloned_cart Cloned cart.
			 * @param array       $input       Input info.
			 * @param \WPGraphQL\AppContext  $context     Context passed.
			 * @param \GraphQL\Type\Definition\ResolveInfo $info        Resolver info passed.
			 */
			do_action( 'graphql_woocommerce_before_empty_cart', $cloned_cart, $input, $context, $info );

			// Empty cart.
			$clear_persistent_cart = ! empty( $input['clearPersistentCart'] ) ? $input['clearPersistentCart'] : true;
			\WC()->cart->empty_cart( $clear_persistent_cart );

			/**
			 * Action fired after cart was cleared/emptied.
			 *
			 * @param object      $cloned_cart Cloned cart.
			 * @param array       $input       Input info.
			 * @param \WPGraphQL\AppContext  $context     Context passed.
			 * @param \GraphQL\Type\Definition\ResolveInfo $info        Resolver info passed.
			 */
			do_action( 'graphql_woocommerce_after_empty_cart', $cloned_cart, $input, $context, $info );

			return [ 'cart' => $cloned_cart ];
		};
	}
}
