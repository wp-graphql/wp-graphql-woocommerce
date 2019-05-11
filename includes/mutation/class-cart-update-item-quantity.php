<?php
/**
 * Mutation - updateItemQuantity
 *
 * Registers mutation for updating a cart item's quantity.
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
 * Class - Cart_Update_Item_Quantity
 */
class Cart_Update_Item_Quantity {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateItemQuantity',
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
		return array_merge(
			array(
				'key'      => array(
					'type'        => array( 'non_null' => 'ID' ),
					'description' => __( 'Cart item being updated', 'wp-graphql-woocommerce' ),
				),
				'quantity' => array(
					'type'        => array( 'non_null' => 'Int' ),
					'description' => __( 'Cart item\'s new quantity', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return Cart_Add_Item::get_output_fields();
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			// Retrieve product database ID if relay ID provided.
			if ( empty( $input['key'] ) ) {
				throw new UserError( __( 'No cart item key provided', 'wp-graphql-woocommerce' ) );
			}

			// Retrieve product database ID if relay ID provided.
			if ( empty( $input['quantity'] ) ) {
				throw new UserError( __( 'No new quantity provided', 'wp-graphql-woocommerce' ) );
			}

			// Get WC_Cart instance.
			$success = WC()->cart->set_quantity( $input['key'], $input['quantity'] );

			if ( true !== $success ) {
				throw new UserError( __( 'Cart item failed to update', 'wp-graphql-woocommerce' ) );
			}

			// Return payload.
			return array( 'key' => $input['key'] );
		};
	}
}
