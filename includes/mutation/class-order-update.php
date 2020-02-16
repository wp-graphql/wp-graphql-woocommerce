<?php
/**
 * Mutation - updateOrder
 *
 * Registers mutation for updating an existing order.
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
use WC_Order_Factory;

/**
 * Class Order_Update
 */
class Order_Update {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateOrder',
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
			Order_Create::get_input_fields(),
			array(
				'id'         => array(
					'type'        => 'ID',
					'description' => __( 'Order global ID', 'wp-graphql-woocommerce' ),
				),
				'orderId'    => array(
					'type'        => 'Int',
					'description' => __( 'Order WP ID', 'wp-graphql-woocommerce' ),
				),
				'customerId' => array(
					'type'        => 'Int',
					'description' => __( 'Order customer ID', 'wp-graphql-woocommerce' ),
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
					return new Order( $payload['id'] );
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

			// Check if authorized to update this order.
			if ( ! Order_Mutation::authorized( 'update', $order_id, $input, $context, $info ) ) {
				throw new UserError( __( 'User does not have the capabilities necessary to update an order.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Action called before order is updated.
			 *
			 * @param int         $order_id  Order ID.
			 * @param array       $input     Input data describing order
			 * @param AppContext  $context   Request AppContext instance.
			 * @param ResolveInfo $info      Request ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_before_order_update', $order_id, $input, $context, $info );

			Order_Mutation::add_order_meta( $order_id, $input, $context, $info );
			Order_Mutation::add_items( $input, $order_id, $context, $info );

			// Apply coupons.
			if ( ! empty( $input['coupons'] ) ) {
				Order_Mutation::apply_coupons( $order_id, $input['coupons'] );
			}

			$order = WC_Order_Factory::get_order( $order_id );

			// Make sure gateways are loaded so hooks from gateways fire on save/create.
			\WC()->payment_gateways();

			// Validate customer ID.
			if ( ! empty( $input['customerId'] ) && ! Order_Mutation::validate_customer( $input ) ) {
				throw new UserError( __( 'New customer ID is invalid.', 'wp-graphql-woocommerce' ) );
			}

			$order->set_created_via( 'graphql-api' );
			$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
			$order->calculate_totals( true );

			// Set status.
			if ( ! empty( $input['status'] ) ) {
				$order->set_status( $input['status'] );
			}

			// Actions for after the order is saved.
			if ( true === $input['isPaid'] ) {
				$order->payment_complete(
					! empty( $input['transactionId'] ) ?
						$input['transactionId']
						: ''
				);
			}

			/**
			 * Action called after order is updated.
			 *
			 * @param WC_Order    $order   WC_Order instance.
			 * @param array       $input   Input data describing order
			 * @param AppContext  $context Request AppContext instance.
			 * @param ResolveInfo $info    Request ResolveInfo instance.
			 */
			do_action( 'graphql_woocommerce_after_order_update', $order, $input, $context, $info );

			return array( 'id' => $order->get_id() );
		};
	}
}
