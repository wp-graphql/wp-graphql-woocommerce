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
				'description' => static function () {
					return __( 'Cart item quantity', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'productId'   => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => static function () {
							return __( 'Cart item product database ID or global ID', 'graphql-for-ecommerce' );
						},
					],
					'quantity'    => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Cart item quantity', 'graphql-for-ecommerce' );
						},
					],
					'variationId' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Cart item product variation database ID or global ID', 'graphql-for-ecommerce' );
						},
					],
					'variation'   => [
						'type'        => [ 'list_of' => 'ProductAttributeInput' ],
						'description' => static function () {
							return __( 'Cart item product variation attributes', 'graphql-for-ecommerce' );
						},
					],
					'extraData'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'JSON string representation of extra cart item data', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
