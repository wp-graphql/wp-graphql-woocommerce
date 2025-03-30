<?php
/**
 * WPInputObjectType - CartItemInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.8.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Cart_Item_Input
 */
class Cart_Item_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'CartItemInput',
			[
				'description' => __( 'Cart item quantity', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'productId'   => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => __( 'Cart item product database ID or global ID', 'wp-graphql-woocommerce' ),
					],
					'quantity'    => [
						'type'        => 'Int',
						'description' => __( 'Cart item quantity', 'wp-graphql-woocommerce' ),
					],
					'variationId' => [
						'type'        => 'Int',
						'description' => __( 'Cart item product variation database ID or global ID', 'wp-graphql-woocommerce' ),
					],
					'variation'   => [
						'type'        => [ 'list_of' => 'ProductAttributeInput' ],
						'description' => __( 'Cart item product variation attributes', 'wp-graphql-woocommerce' ),
					],
					'extraData'   => [
						'type'        => 'String',
						'description' => __( 'JSON string representation of extra cart item data', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
