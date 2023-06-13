<?php
/**
 * WPInputObjectType - CartItemQuantityInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Cart_Item_Quantity_Input
 */
class Cart_Item_Quantity_Input {

	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'CartItemQuantityInput',
			[
				'description' => __( 'Cart item quantity', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'key'      => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Cart item being updated', 'wp-graphql-woocommerce' ),
					],
					'quantity' => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => __( 'Cart item\'s new quantity', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
