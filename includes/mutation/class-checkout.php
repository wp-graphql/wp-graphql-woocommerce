<?php
/**
 * Mutation - checkout
 *
 * Registers mutation for checking out.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Checkout_Mutation;
use WPGraphQL\WooCommerce\Data\Mutation\Order_Mutation;
use WPGraphQL\WooCommerce\Model\Order;
use WPGraphQL\WooCommerce\Model\Customer;
use Exception;

/**
 * Class Checkout
 */
class Checkout {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'checkout',
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
			'paymentMethod'          => [
				'type'        => 'String',
				'description' => __( 'Payment method ID.', 'wp-graphql-woocommerce' ),
			],
			'shippingMethod'         => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Order shipping method', 'wp-graphql-woocommerce' ),
			],
			'shipToDifferentAddress' => [
				'type'        => 'Boolean',
				'description' => __( 'Ship to a separate address', 'wp-graphql-woocommerce' ),
			],
			'billing'                => [
				'type'        => 'CustomerAddressInput',
				'description' => __( 'Order billing address', 'wp-graphql-woocommerce' ),
			],
			'shipping'               => [
				'type'        => 'CustomerAddressInput',
				'description' => __( 'Order shipping address', 'wp-graphql-woocommerce' ),
			],
			'account'                => [
				'type'        => 'CreateAccountInput',
				'description' => __( 'Create new customer account', 'wp-graphql-woocommerce' ),
			],
			'transactionId'          => [
				'type'        => 'String',
				'description' => __( 'Order transaction ID', 'wp-graphql-woocommerce' ),
			],
			'isPaid'                 => [
				'type'        => 'Boolean',
				'description' => __( 'Define if the order is paid. It will set the status to processing and reduce stock items.', 'wp-graphql-woocommerce' ),
			],
			'metaData'               => [
				'type'        => [ 'list_of' => 'MetaDataInput' ],
				'description' => __( 'Order meta data', 'wp-graphql-woocommerce' ),
			],
			'customerNote'           => [
				'type'        => 'String',
				'description' => __( 'Order customer note', 'wp-graphql-woocommerce' ),
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
			'order'    => [
				'type'    => 'Order',
				'resolve' => function( $payload ) {
					return new Order( $payload['id'] );
				},
			],
			'customer' => [
				'type'    => 'Customer',
				'resolve' => function() {
					return is_user_logged_in() ? new Customer( get_current_user_id() ) : null;
				},
			],
			'result'   => [
				'type'    => 'String',
				'resolve' => function( $payload ) {
					return $payload['result'];
				},
			],
			'redirect' => [
				'type'    => 'String',
				'resolve' => function( $payload ) {
					return $payload['redirect'];
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
		return function( $input, AppContext $context, ResolveInfo $info ) {
			// Create order.
			$order = null;
			try {
				$args = Checkout_Mutation::prepare_checkout_args( $input, $context, $info );

				/**
				 * Action called before checking out.
				 *
				 * @param array       $args    Order data.
				 * @param array       $input   Raw input data .
				 * @param AppContext  $context Request AppContext instance.
				 * @param ResolveInfo $info    Request ResolveInfo instance.
				 */
				do_action( 'graphql_woocommerce_before_checkout', $args, $input, $context, $info );

				$order_id = Checkout_Mutation::process_checkout( $args, $input, $context, $info, $results );

				if ( is_wp_error( $order_id ) ) {
					throw new UserError( $order_id->get_error_message( 'checkout-error' ) );
				}

				$order = \WC_Order_Factory::get_order( $order_id );
				/**
				 * Action called after checking out.
				 *
				 * @param WC_Order    $order   WC_Order instance.
				 * @param array       $input   Input data describing order.
				 * @param AppContext  $context Request AppContext instance.
				 * @param ResolveInfo $info    Request ResolveInfo instance.
				 */
				do_action( 'graphql_woocommerce_after_checkout', $order, $input, $context, $info );

				return array_merge( [ 'id' => $order_id ], $results );
			} catch ( Exception $e ) {
				Order_Mutation::purge( $order );
				throw new UserError( $e->getMessage() );
			}//end try
		};
	}
}
