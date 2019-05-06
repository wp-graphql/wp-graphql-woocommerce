<?php
/**
 * Mutation - emptyCart
 *
 * Registers mutation for empty cart of all contents including coupons and fees.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

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
			'cart' => array(
				'type'    => 'Cart',
				'resolve' => function ( $payload ) {
					return $payload['cart'];
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
			// Get WC_Cart instance.
			$cloned_cart = clone \WC()->cart;

			// Empty cart.
			\WC()->cart->empty_cart();

			return array( 'cart' => $cloned_cart );
		};
	}
}
