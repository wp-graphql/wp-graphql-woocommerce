<?php
/**
 * Mutation - deleteOrderNote
 *
 * Registers mutation for delete an order note.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Comment;
use WPGraphQL\Utils\Utils;
use WPGraphQL\WooCommerce\Data\Mutation\Order_Mutation;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Order_Note_Delete
 */
class Order_Note_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteOrderNote',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'id'      => [
				'type'        => 'ID',
				'description' => static function () {
					return __( 'Database ID or global ID of the order note', 'wp-graphql-woocommerce' );
				},
			],
			'orderId' => [
				'type'        => 'ID',
				'description' => static function () {
					return __( 'Database ID or global ID of the order', 'wp-graphql-woocommerce' );
				},
			],
			'force'   => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Delete or simply place in trash.', 'wp-graphql-woocommerce' );
				},
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'orderNote' => [
				'type'    => 'OrderNote',
				'resolve' => static function ( $payload ) {
					return new Comment( $payload['note'] );
				},
			],
			'order'     => [
				'type'    => 'Order',
				'resolve' => static function ( $payload ) {
					return new Order( $payload['order_id'] );
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
			// Retrieve order ID.
			$order_id = Utils::get_database_id_from_id( $input['orderId'] );

			if ( ! $order_id ) {
				throw new UserError( __( 'Order ID provided is invalid. Please check input and try again.', 'wp-graphql-woocommerce' ) );
			}

			// Check if authorized to delete this order note.
			if ( ! Order_Mutation::authorized( $input, $context, $info, 'delete', $order_id ) ) {
				throw new UserError( __( 'User does not have the capabilities necessary to delete an order.', 'wp-graphql-woocommerce' ) );
			}

			if ( isset( $input['forceDelete'] ) && false === $input['forceDelete'] ) {
				throw new UserError( __( 'woocommerce_rest_trash_not_supported', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Get Order model instance for output.
			 *
			 * @var \WC_Order $order
			 */
			$order = new Order( $order_id );

			if ( ! $order ) {
				throw new UserError( __( 'Invalid order ID.', 'wp-graphql-woocommerce' ) );
			}

			$id = Utils::get_database_id_from_id( $input['id'] );
			if ( ! $id ) {
				throw new UserError( __( 'Order note ID provided is invalid. Please check input and try again.', 'wp-graphql-woocommerce' ) );
			}

			$note = get_comment( $id );

			if ( empty( $note ) || intval( $note->comment_post_ID ) !== intval( $order->get_id() ) ) {
				throw new UserError( __( 'Invalid resource ID.', 'wp-graphql-woocommerce' ) );
			}

			$comment_id = absint( $note->comment_ID );
			$result     = wc_delete_order_note( $comment_id );

			if ( ! $result ) {
				throw new UserError( __( 'Unable to delete order note.', 'wp-graphql-woocommerce' ) );
			}

			return [
				'order_id' => $order_id,
				'note'     => $note,
			];
		};
	}
}
