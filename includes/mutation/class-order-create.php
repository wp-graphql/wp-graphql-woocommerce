<?php
/**
 * Mutation - createOrder
 *
 * Registers mutation for creating an order.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WC_Order_Factory;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Order_Mutation;
use WPGraphQL\WooCommerce\Model\Order;
use WPGraphQL\WooCommerce\WooCommerce;

/**
 * Class Order_Create
 */
class Order_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createOrder',
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
			'parentId'           => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Parent order ID.', 'graphql-for-ecommerce' );
				},
			],
			'currency'           => [
				'type'        => 'CurrencyEnum',
				'description' => static function () {
					return __( 'Currency the order was created with, in ISO format.', 'graphql-for-ecommerce' );
				},
			],
			'customerId'         => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Order customer ID', 'graphql-for-ecommerce' );
				},
			],
			'customerNote'       => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Note left by customer during checkout.', 'graphql-for-ecommerce' );
				},
			],
			'coupons'            => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => static function () {
					return __( 'Coupons codes to be applied to order', 'graphql-for-ecommerce' );
				},
			],
			'status'             => [
				'type'        => 'OrderStatusEnum',
				'description' => static function () {
					return __( 'Order status', 'graphql-for-ecommerce' );
				},
			],
			'paymentMethod'      => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Payment method ID.', 'graphql-for-ecommerce' );
				},
			],
			'paymentMethodTitle' => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Payment method title.', 'graphql-for-ecommerce' );
				},
			],
			'transactionId'      => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Order transaction ID', 'graphql-for-ecommerce' );
				},
			],
			'billing'            => [
				'type'        => 'CustomerAddressInput',
				'description' => static function () {
					return __( 'Order billing address', 'graphql-for-ecommerce' );
				},
			],
			'shipping'           => [
				'type'        => 'CustomerAddressInput',
				'description' => static function () {
					return __( 'Order shipping address', 'graphql-for-ecommerce' );
				},
			],
			'lineItems'          => [
				'type'        => [ 'list_of' => 'LineItemInput' ],
				'description' => static function () {
					return __( 'Order line items', 'graphql-for-ecommerce' );
				},
			],
			'shippingLines'      => [
				'type'        => [ 'list_of' => 'ShippingLineInput' ],
				'description' => static function () {
					return __( 'Order shipping lines', 'graphql-for-ecommerce' );
				},
			],
			'feeLines'           => [
				'type'        => [ 'list_of' => 'FeeLineInput' ],
				'description' => static function () {
					return __( 'Order shipping lines', 'graphql-for-ecommerce' );
				},
			],
			'metaData'           => [
				'type'        => [ 'list_of' => 'MetaDataInput' ],
				'description' => static function () {
					return __( 'Order meta data', 'graphql-for-ecommerce' );
				},
			],
			'isPaid'             => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Define if the order is paid. It will set the status to processing and reduce stock items.', 'graphql-for-ecommerce' );
				},
			],
			'createdVia'         => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Source of the order. Useful when WooCommerce is driven from multiple sources. Defaults to "graphql-api".', 'graphql-for-ecommerce' );
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
			'order'   => [
				'type'    => 'Order',
				'resolve' => static function ( $payload ) {
					return new Order( $payload['id'] );
				},
			],
			'orderId' => [
				'type'    => 'Int',
				'resolve' => static function ( $payload ) {
					return $payload['id'];
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
			// Check if authorized to create this order.
			if ( ! Order_Mutation::authorized( $input, $context, $info, 'create', null ) ) {
				throw new UserError( __( 'User does not have the capabilities necessary to create an order.', 'graphql-for-ecommerce' ) );
			}

			// Create order.
			$order = null;
			try {
				$order_id = Order_Mutation::create_order( $input, $context, $info );
				$order    = WC_Order_Factory::get_order( $order_id );

				if ( ! is_object( $order ) ) {
					throw new UserError( __( 'Order could not be created.', 'graphql-for-ecommerce' ) );
				}

				// Make sure gateways are loaded so hooks from gateways fire on save/create.
				WC()->payment_gateways();

				// Validate customer ID, if set.
				if ( ! empty( $input['customerId'] ) && ! Order_Mutation::validate_customer( $input['customerId'] ) ) {
					throw new UserError( __( 'Customer ID is invalid.', 'graphql-for-ecommerce' ) );
				}

				// Set all props, address, items, and meta on the order and save once.
				Order_Mutation::prepare_order( $order, $input, $context, $info );

				// Apply coupons.
				if ( ! empty( $input['coupons'] ) ) {
					Order_Mutation::apply_coupons( $order, $input['coupons'] );
				}

				$created_via = ! empty( $input['createdVia'] ) ? $input['createdVia'] : WooCommerce::get_order_attribution_source_type();
				$order->set_created_via( $created_via );
				$order->add_meta_data( '_wc_order_attribution_source_type', $created_via, true );
				$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
				$order->calculate_totals( true );

				// Set status.
				if ( ! empty( $input['status'] ) ) {
					$order->set_status( $input['status'] );
				}

				// Actions for after the order is saved.
				if ( ! empty( $input['isPaid'] ) ) {
					$transaction_id = ! empty( $input['transactionId'] ) ? $input['transactionId'] : '';
					$order->payment_complete(
						apply_filters( 'graphql_woocommerce_order_pre_validate_transaction_id', $transaction_id, $order, $input )
					);
				}

				/**
				 * Action called after order is created.
				 *
				 * @param \WC_Order    $order   WC_Order instance.
				 * @param array       $input   Input data describing order.
				 * @param \WPGraphQL\AppContext  $context Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
				 */
				do_action( 'graphql_woocommerce_after_order_create', $order, $input, $context, $info );

				return [ 'id' => $order->get_id() ];
			} catch ( \Throwable $e ) {
				// Delete order if it was created.
				if ( is_object( $order ) ) {
					Order_Mutation::purge( $order );
				}

				// Throw error.
				throw new UserError( $e->getMessage() );
			}//end try
		};
	}
}
