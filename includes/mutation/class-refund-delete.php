<?php
/**
 * Mutation - deleteRefund
 *
 * Registers mutation for deleting a refund on an order.
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
 * Class Refund_Delete
 */
class Refund_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteRefund',
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
			'id'    => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => static function () {
					return __( 'The ID of the refund to delete.', 'wp-graphql-woocommerce' );
				},
			],
			'force' => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Force delete the refund. Defaults to true.', 'wp-graphql-woocommerce' );
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
					return ! empty( $payload['refund'] ) ? $payload['refund'] : null;
				},
			],
			'order'  => [
				'type'    => 'Order',
				'resolve' => static function ( $payload ) {
					return ! empty( $payload['order_id'] ) ? new Order( $payload['order_id'] ) : null;
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
			$refund_id = \WPGraphQL\Utils\Utils::get_database_id_from_id( $input['id'] );

			if ( empty( $refund_id ) ) {
				throw new UserError( __( 'Invalid refund ID.', 'wp-graphql-woocommerce' ) );
			}

			/** @var \WC_Order_Refund|false $refund */
			$refund = \wc_get_order( $refund_id );
			if ( ! $refund || 'shop_order_refund' !== $refund->get_type() ) {
				throw new UserError( __( 'Invalid refund ID.', 'wp-graphql-woocommerce' ) );
			}

			$order_id = $refund->get_parent_id();
			if ( ! \wc_rest_check_post_permissions( 'shop_order', 'delete', $order_id ) ) {
				throw new UserError( __( 'You do not have permission to delete this refund.', 'wp-graphql-woocommerce' ) );
			}

			// Capture refund data before deletion for the response.
			$refund_model = new Order( $refund_id );

			/**
			 * Action called before a refund is deleted.
			 *
			 * @param int                                  $refund_id Refund ID.
			 * @param int                                  $order_id  Order ID.
			 * @param array                                $input     Input data.
			 * @param \WPGraphQL\AppContext                $context   AppContext instance.
			 * @param \GraphQL\Type\Definition\ResolveInfo $info      ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_before_refund_delete', $refund_id, $order_id, $input, $context, $info );

			$force  = isset( $input['force'] ) ? $input['force'] : true;
			$result = $refund->delete( $force );

			if ( ! $result ) {
				throw new UserError( __( 'Could not delete refund.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Action called after a refund is deleted.
			 *
			 * @param int                                  $refund_id Refund ID.
			 * @param int                                  $order_id  Order ID.
			 * @param array                                $input     Input data.
			 * @param \WPGraphQL\AppContext                $context   AppContext instance.
			 * @param \GraphQL\Type\Definition\ResolveInfo $info      ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_after_refund_delete', $refund_id, $order_id, $input, $context, $info );

			return [
				'refund'   => $refund_model,
				'order_id' => $order_id,
			];
		};
	}
}
