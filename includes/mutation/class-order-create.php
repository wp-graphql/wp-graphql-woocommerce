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
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Order_Mutation;
use WPGraphQL\WooCommerce\Model\Order;
use WC_Order_Factory;
use Exception;

/**
 * Class Order_Create
 */
class Order_Create {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createOrder',
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
		$input_fields = array(
			'parentId'           => array(
				'type'        => 'Int',
				'description' => __( 'Parent order ID.', 'wp-graphql-woocommerce' ),
			),
			'currency'           => array(
				'type'        => 'String',
				'description' => __( 'Currency the order was created with, in ISO format.', 'wp-graphql-woocommerce' ),
			),
			'customerId'         => array(
				'type'        => 'Int',
				'description' => __( 'Order customer ID', 'wp-graphql-woocommerce' ),
			),
			'customerNote'       => array(
				'type'        => 'String',
				'description' => __( 'Note left by customer during checkout.', 'wp-graphql-woocommerce' ),
			),
			'coupons'            => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Coupons codes to be applied to order', 'wp-graphql-woocommerce' ),
			),
			'status'             => array(
				'type'        => 'OrderStatusEnum',
				'description' => __( 'Order status', 'wp-graphql-woocommerce' ),
			),
			'paymentMethod'      => array(
				'type'        => 'String',
				'description' => __( 'Payment method ID.', 'wp-graphql-woocommerce' ),
			),
			'paymentMethodTitle' => array(
				'type'        => 'String',
				'description' => __( 'Payment method title.', 'wp-graphql-woocommerce' ),
			),
			'transactionId'      => array(
				'type'        => 'String',
				'description' => __( 'Order transaction ID', 'wp-graphql-woocommerce' ),
			),
			'billing'            => array(
				'type'        => 'CustomerAddressInput',
				'description' => __( 'Order billing address', 'wp-graphql-woocommerce' ),
			),
			'shipping'           => array(
				'type'        => 'CustomerAddressInput',
				'description' => __( 'Order shipping address', 'wp-graphql-woocommerce' ),
			),
			'lineItems'          => array(
				'type'        => array( 'list_of' => 'LineItemInput' ),
				'description' => __( 'Order line items', 'wp-graphql-woocommerce' ),
			),
			'shippingLines'      => array(
				'type'        => array( 'list_of' => 'ShippingLineInput' ),
				'description' => __( 'Order shipping lines', 'wp-graphql-woocommerce' ),
			),
			'feeLines'           => array(
				'type'        => array( 'list_of' => 'FeeLineInput' ),
				'description' => __( 'Order shipping lines', 'wp-graphql-woocommerce' ),
			),
			'metaData'           => array(
				'type'        => array( 'list_of' => 'MetaDataInput' ),
				'description' => __( 'Order meta data', 'wp-graphql-woocommerce' ),
			),
			'isPaid'             => array(
				'type'        => 'Boolean',
				'description' => __( 'Define if the order is paid. It will set the status to processing and reduce stock items.', 'wp-graphql-woocommerce' ),
			),
		);

		return $input_fields;
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'order'   => array(
				'type'    => 'Order',
				'resolve' => function( $payload ) {
					return new Order( $payload['id'] );
				},
			),
			'orderId' => array(
				'type'    => 'Int',
				'resolve' => function( $payload ) {
					return $payload['id'];
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
			// Check if authorized to create this order.
			if ( ! Order_Mutation::authorized( 'create', null, $input, $context, $info ) ) {
				throw new UserError( __( 'User does not have the capabilities necessary to create an order.', 'wp-graphql-woocommerce' ) );
			}

			// Create order.
			$order = null;
			try {
				$order_id = Order_Mutation::create_order( $input, $context, $info );
				Order_Mutation::add_order_meta( $order_id, $input, $context, $info );
				Order_Mutation::add_items( $input, $order_id, $context, $info );

				// Apply coupons.
				if ( ! empty( $input['coupons'] ) ) {
					Order_Mutation::apply_coupons( $order_id, $input['coupons'] );
				}

				$order = WC_Order_Factory::get_order( $order_id );

				// Make sure gateways are loaded so hooks from gateways fire on save/create.
				WC()->payment_gateways();

				// Validate customer ID, if set.
				if ( ! empty( $input['customerId'] ) && ! Order_Mutation::validate_customer( $input ) ) {
					throw new UserError( __( 'Customer ID is invalid.', 'wp-graphql-woocommerce' ) );
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
						! empty( $input['transactionId'] ) ? $input['transactionId'] : ''
					);
				}

				/**
				 * Action called after order is created.
				 *
				 * @param WC_Order    $order   WC_Order instance.
				 * @param array       $input   Input data describing order.
				 * @param AppContext  $context Request AppContext instance.
				 * @param ResolveInfo $info    Request ResolveInfo instance.
				 */
				do_action( 'graphql_woocommerce_after_order_create', $order, $input, $context, $info );

				return array( 'id' => $order->get_id() );
			} catch ( Exception $e ) {
				Order_Mutation::purge( $order );
				throw new UserError( $e->getMessage() );
			}
		};
	}
}
