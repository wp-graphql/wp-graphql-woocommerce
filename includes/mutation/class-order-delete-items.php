<?php
/**
 * Mutation - deleteOrderItems
 *
 * Registers mutation for delete an items from an order.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Order_Mutation;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Order_Delete_Items
 */
class Order_Delete_Items {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteOrderItems',
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
				'id'      => array(
					'type'        => 'ID',
					'description' => __( 'Order global ID', 'wp-graphql-woocommerce' ),
				),
				'orderId' => array(
					'type'        => 'Int',
					'description' => __( 'Order WP ID', 'wp-graphql-woocommerce' ),
				),
				'itemIds' => array(
					'type'        => array( 'list_of' => 'Int' ),
					'description' => __( 'ID Order items being deleted', 'wp-graphql-woocommerce' ),
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
		return array(
			'order' => array(
				'type'    => 'Order',
				'resolve' => function( $payload ) {
					return $payload['order'];
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
			// Retrieve order ID.
			$order_id = null;
			if ( ! empty( $input['id'] ) ) {
				$id_components = Relay::fromGlobalId( $input['id'] );
				if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
					throw new UserError( __( 'The "id" provided is invalid', 'wp-graphql-woocommerce' ) );
				}
				$order_id = absint( $id_components['id'] );
			} elseif ( ! empty( $input['orderId'] ) ) {
				$order_id = absint( $input['orderId'] );
			} else {
				throw new UserError( __( 'No order ID provided.', 'wp-graphql-woocommerce' ) );
			}

			// Check if authorized to delete items on this order.
			if ( ! Order_Mutation::authorized( 'delete-items', $order_id, $input, $context, $info ) ) {
				throw new UserError( __( 'User does not have the capabilities necessary to delete an order.', 'wp-graphql-woocommerce' ) );
			}

			// Confirm item IDs.
			if ( empty( $input['itemIds'] ) ) {
				throw new UserError( __( 'No item IDs provided.', 'wp-graphql-woocommerce' ) );
			} elseif ( ! is_array( $input['itemIds'] ) ) {
				throw new UserError( __( 'The "itemIds" provided is invalid', 'wp-graphql-woocommerce' ) );
			}
			$ids = $input['itemIds'];

			// Get Order model instance for output.
			$order = new Order( $order_id );

			// Cache items to prevent null value errors.
			// @codingStandardsIgnoreStart
			$order->downloadableItems;
			$order->get_items();
			$order->get_items( 'fee' );
			$order->get_items( 'shipping' );
			$order->get_items( 'tax' );
			$order->get_items( 'coupon' );
			// @codingStandardsIgnoreEnd.

			// Get working order model.
			$working_order = new Order( $order_id );

			/**
			 * Action called before order is deleted.
			 *
			 * @param WC_Order    $order   WC_Order instance.
			 * @param array       $input   Input data describing order.
			 * @param AppContext  $context Request AppContext instance.
			 * @param ResolveInfo $info    Request ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_before_order_items_delete', $ids, $working_order, $input, $context, $info );

			// Delete order.
			$errors = '';
			foreach ( $ids as $id ) {
				$working_order->remove_item( $id );
			}
			$working_order->save();

			/**
			 * Action called before order is deleted.
			 *
			 * @param WC_Order    $order   WC_Order instance.
			 * @param array       $input   Input data describing order
			 * @param AppContext  $context Request AppContext instance.
			 * @param ResolveInfo $info    Request ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_after_order_delete', $ids, $working_order, $input, $context, $info );

			return array( 'order' => $order );
		};
	}
}
