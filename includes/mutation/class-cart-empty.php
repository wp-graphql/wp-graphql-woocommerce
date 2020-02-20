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

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Empty
 */
class Cart_Empty {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'emptyCart',
			array(
				'inputFields'         => array(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'cart' => Cart_Mutation::get_cart_field(),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			Cart_Mutation::check_session_token();

			// Get/Clone WC_Cart instance.
			$cloned_cart = clone \WC()->cart;

			/**
			 * Action fired before cart was cleared/emptied.
			 *
			 * @param object      $cloned_cart Cloned cart.
			 * @param array       $input       Input info.
			 * @param AppContext  $context     Context passed.
			 * @param ResolveInfo $info        Resolver info passed.
			 */
			do_action( 'graphql_woocommerce_before_empty_cart', $cloned_cart, $input, $context, $info );

			// Empty cart.
			\WC()->cart->empty_cart();

			/**
			 * Action fired after cart was cleared/emptied.
			 *
			 * @param object      $cloned_cart Cloned cart.
			 * @param array       $input       Input info.
			 * @param AppContext  $context     Context passed.
			 * @param ResolveInfo $info        Resolver info passed.
			 */
			do_action( 'graphql_woocommerce_after_empty_cart', $cloned_cart, $input, $context, $info );

			return array( 'cart' => $cloned_cart );
		};
	}
}
