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
	 */
	public static function register() {
		register_graphql_input_type(
			'CartItemInput',
			array(
				'description' => __( 'Cart item quantity', 'wp-graphql-woocommerce' ),
				'fields'      => array(
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
				),
			)
		);
	}
}
