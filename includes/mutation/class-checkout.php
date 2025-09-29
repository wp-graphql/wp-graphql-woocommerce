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
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Checkout
 */
class Checkout {
	/**
	 * Registers mutation
	 *
	 * @return void
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
			'fees'                   => [
				'type'        => [ 'list_of' => 'FeeInput' ],
				'description' => __( 'Fees to add to the order.', 'wp-graphql-woocommerce' ),
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
				'resolve' => static function ( $payload ) {
					return new Order( $payload['id'] );
				},
			],
			'customer' => [
				'type'    => 'Customer',
				'resolve' => static function () {
					return is_user_logged_in() ? new Customer( get_current_user_id() ) : new Customer();
				},
			],
			'result'   => [
				'type'    => 'String',
				'resolve' => static function ( $payload ) {
					return $payload['result'];
				},
			],
			'redirect' => [
				'type'    => 'String',
				'resolve' => static function ( $payload ) {
					return $payload['redirect'];
				},
			],
			'notices'  => [
				'type'        => [ 'list_of' => 'CartNotice' ],
				'description' => __( 'WooCommerce notices generated during checkout', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					return $payload['notices'] ?? [];
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
			// Create order.
			$order = null;
			try {
				$args = Checkout_Mutation::prepare_checkout_args( $input, $context, $info );

				/**
				 * Action called before checking out.
				 *
				 * @param array       $args    Order data.
				 * @param array       $input   Raw input data .
				 * @param \WPGraphQL\AppContext  $context Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
				 */
				do_action( 'graphql_woocommerce_before_checkout', $args, $input, $context, $info );

				// We define this now and pass it as a reference.
				$results = [];

				$order_id = Checkout_Mutation::process_checkout( $args, $input, $context, $info, $results );

				$order = \WC_Order_Factory::get_order( $order_id );

				if ( ! is_object( $order ) ) {
					throw new UserError( __( 'Failed to retrieve order after checkout', 'wp-graphql-woocommerce' ) );
				}//end if

				/**
				 * Action called after checking out.
				 *
				 * @param \WC_Order   $order   WC_Order instance.
				 * @param array       $input   Input data describing order.
				 * @param \WPGraphQL\AppContext  $context Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
				 */
				// Capture any non-error notices for successful checkouts
				$notices = wc_get_notices();
				$formatted_notices = self::format_notices_for_response( $notices );

				// Clear notices to prevent persistence
				wc_clear_notices();

				do_action( 'graphql_woocommerce_after_checkout', $order, $input, $context, $info );

				return array_merge( [ 'id' => $order_id ], $results, [ 'notices' => $formatted_notices ] );
			} catch ( \Throwable $e ) {
				// Delete order if it was created.
				if ( is_object( $order ) ) {
					Order_Mutation::purge( $order );
				}

				// Capture any WC notices that were added during checkout process
				$notices = wc_get_notices();
				$error_message = $e->getMessage();

				// If there are notices, use them instead of the original error
				if ( ! empty( $notices ) ) {
					$formatted_notices = self::format_notices_for_error( $notices );
					if ( ! empty( $formatted_notices ) ) {
						$error_message = $formatted_notices;
					}
				}

				// Clear notices to prevent them from persisting to next request
				wc_clear_notices();

				// Throw error with enhanced message
				throw new UserError( $error_message );
			}//end try
		};
	}

	/**
	 * Format WC notices for GraphQL response
	 *
	 * @param array $notices WC notices array
	 * @return array Formatted notices for GraphQL
	 */
	private static function format_notices_for_response( $notices ) {
		$formatted_notices = [];

		// Include non-error notices (success, notice)
		foreach ( [ 'success', 'notice' ] as $type ) {
			if ( ! empty( $notices[ $type ] ) ) {
				foreach ( $notices[ $type ] as $notice ) {
					$formatted_notices[] = [
						'type'    => $type,
						'message' => $notice['notice'] ?? $notice,
					];
				}
			}
		}

		return $formatted_notices;
	}

	/**
	 * Format WC notices for error reporting
	 *
	 * @param array $notices WC notices array
	 * @return string Formatted error message
	 */
	private static function format_notices_for_error( $notices ) {
		$error_messages = [];

		// Prioritize error notices
		if ( ! empty( $notices['error'] ) ) {
			foreach ( $notices['error'] as $notice ) {
				$error_messages[] = $notice['notice'] ?? $notice;
			}
		}

		// Include other notice types if no errors
		if ( empty( $error_messages ) ) {
			foreach ( [ 'notice', 'success' ] as $type ) {
				if ( ! empty( $notices[ $type ] ) ) {
					foreach ( $notices[ $type ] as $notice ) {
						$error_messages[] = $notice['notice'] ?? $notice;
					}
				}
			}
		}

		return implode( ' ', $error_messages );
	}
}
