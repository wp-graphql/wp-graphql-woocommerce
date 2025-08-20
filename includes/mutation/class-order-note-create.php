<?php
/**
 * Mutation - createOrderNote
 *
 * Registers mutation for create an order note.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Utils\Utils;
use WPGraphQL\WooCommerce\Data\Mutation\Order_Mutation;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Order_Note_Create
 */
class Order_Note_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createOrderNote',
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
			'orderId'        => [
				'type'        => 'ID',
				'description' => __( 'Database ID or global ID of the order', 'wp-graphql-woocommerce' ),
			],
			'note'           => [
				'type'        => 'String',
				'description' => __( 'Order note.', 'wp-graphql-woocommerce' ),
			],
			'isCustomerNote' => [
				'type'        => 'Boolean',
				'description' => __( 'Shows/define if the note is only for reference or for the customer (the user will be notified).', 'wp-graphql-woocommerce' ),
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
					return $payload['note'];
				},
			],
			'order'     => [
				'type'    => 'Order',
				'resolve' => static function ( $payload ) {
					return $payload['order'];
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

			// Check if authorized to create order notes.
			if ( ! Order_Mutation::authorized( $input, $context, $info, 'create', $order_id ) ) {
				throw new UserError( __( 'User does not have the capabilities necessary to create an order note.', 'wp-graphql-woocommerce' ) );
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

			$note_content = ! empty( $input['note'] ) ? $input['note'] : '';
			if ( empty( $note_content ) ) {
				throw new UserError( __( 'Order note content is required.', 'wp-graphql-woocommerce' ) );
			}

			$is_customer_note = ! empty( $input['isCustomerNote'] ) ? $input['isCustomerNote'] : false;

			// Create the order note.
			$note_id = $order->add_order_note( $note_content, $is_customer_note );

			if ( ! $note_id ) {
				throw new UserError( __( 'Unable to create order note.', 'wp-graphql-woocommerce' ) );
			}

			// Get the created note.
			$note = get_comment( $note_id );
			$note->ID = $note_id;

			if ( ! $note ) {
				throw new UserError( __( 'Unable to retrieve created order note.', 'wp-graphql-woocommerce' ) );
			}

			return [
				'order' => $order,
				'note'  => $note,
			];
		};
	}
}
