<?php
/**
 * Mutation - createRefund
 *
 * Registers mutation for creating a refund on an order.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 1.0.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Refund_Create
 */
class Refund_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createRefund',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration.
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'orderId'       => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'The ID of the order to refund.', 'wp-graphql-woocommerce' );
				},
			],
			'amount'        => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'Refund amount.', 'wp-graphql-woocommerce' );
				},
			],
			'reason'        => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Reason for refund.', 'wp-graphql-woocommerce' );
				},
			],
			'refundPayment' => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'When true, the payment gateway API is used to generate the refund.', 'wp-graphql-woocommerce' );
				},
			],
			'restockItems'  => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'When true, refunded items are restocked.', 'wp-graphql-woocommerce' );
				},
			],
			'metaData'      => [
				'type'        => [ 'list_of' => 'MetaDataInput' ],
				'description' => static function () {
					return __( 'Meta data.', 'wp-graphql-woocommerce' );
				},
			],
		];
	}

	/**
	 * Defines the mutation output field configuration.
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'refund' => [
				'type'    => 'Refund',
				'resolve' => static function ( $payload ) {
					return new Order( $payload['id'] );
				},
			],
			'order'  => [
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
			$order_id = absint( $input['orderId'] );
			$order    = \wc_get_order( $order_id );

			if ( ! $order ) {
				throw new UserError( __( 'Invalid order ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( ! \wc_rest_check_post_permissions( 'shop_order', 'edit', $order_id ) ) {
				throw new UserError( __( 'You do not have permission to create refunds for this order.', 'wp-graphql-woocommerce' ) );
			}

			$amount = floatval( $input['amount'] );
			if ( 0 >= $amount ) {
				throw new UserError( __( 'Refund amount must be greater than zero.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Action called before a refund is created.
			 *
			 * @param int                                  $order_id Order ID.
			 * @param array                                $input    Input data.
			 * @param \WPGraphQL\AppContext                $context  AppContext instance.
			 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_before_refund_create', $order_id, $input, $context, $info );

			$refund = \wc_create_refund(
				[
					'order_id'       => $order_id,
					'amount'         => $amount,
					'reason'         => ! empty( $input['reason'] ) ? $input['reason'] : null,
					'refund_payment' => ! empty( $input['refundPayment'] ) ? $input['refundPayment'] : false,
					'restock_items'  => ! empty( $input['restockItems'] ) ? $input['restockItems'] : false,
				]
			);

			if ( is_wp_error( $refund ) ) {
				throw new UserError( $refund->get_error_message() );
			}

			if ( ! $refund ) {
				throw new UserError( __( 'Could not create refund, please try again.', 'wp-graphql-woocommerce' ) );
			}

			// Set meta data.
			if ( ! empty( $input['metaData'] ) && is_array( $input['metaData'] ) ) {
				foreach ( $input['metaData'] as $meta ) {
					$refund->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
				}
				$refund->save_meta_data();
			}

			/**
			 * Action called after a refund is created.
			 *
			 * @param \WC_Order_Refund                     $refund   Refund object.
			 * @param int                                  $order_id Order ID.
			 * @param array                                $input    Input data.
			 * @param \WPGraphQL\AppContext                $context  AppContext instance.
			 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_after_refund_create', $refund, $order_id, $input, $context, $info );

			return [
				'id'       => $refund->get_id(),
				'order_id' => $order_id,
			];
		};
	}
}
