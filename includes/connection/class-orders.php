<?php
/**
 * Connection - Orders
 *
 * Registers connections to Order
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use Automattic\WooCommerce\Utilities\OrderUtil;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\WooCommerce\Data\Connection\Order_Connection_Resolver;

/**
 * Class - Orders
 */
class Orders {

	/**
	 * Registers the various connections from other Types to Customer
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From RootQuery To Orders.
		register_graphql_connection(
			self::get_connection_config()
		);

		// From Customer To Orders.
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'      => 'Customer',
					'fromFieldName' => 'orders',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Order_Connection_Resolver( $source, $args, $context, $info );

						return self::get_customer_order_connection( $resolver, $source );
					},
				]
			)
		);

		// From RootQuery To Refunds.
		register_graphql_connection(
			self::get_connection_config(
				[
					'toType'         => 'Refund',
					'fromFieldName'  => 'refunds',
					'connectionArgs' => self::get_refund_connection_args(),
				],
				'shop_order_refund'
			)
		);
		// From Order To Refunds.
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'       => 'Order',
					'toType'         => 'Refund',
					'fromFieldName'  => 'refunds',
					'connectionArgs' => self::get_refund_connection_args(),
					'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Order_Connection_Resolver( $source, $args, $context, $info, 'shop_order_refund' );

						$resolver->set_should_execute( true );
						$resolver->set_query_arg( 'parent', $source->ID );

						return $resolver->get_connection();
					},
				],
				'shop_order_refund'
			)
		);
		// From Customer To Refunds.
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'       => 'Customer',
					'toType'         => 'Refund',
					'fromFieldName'  => 'refunds',
					'connectionArgs' => self::get_refund_connection_args(),
					'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Order_Connection_Resolver( $source, $args, $context, $info, 'shop_order_refund' );

						return self::get_customer_refund_connection( $resolver, $source );
					},
				],
				'shop_order_refund'
			)
		);
	}

	/**
	 * Returns order connection filter by customer.
	 *
	 * @param Order_Connection_Resolver $resolver  Connection resolver.
	 * @param \WC_Customer              $customer  Customer object of querying user.
	 *
	 * @return array
	 */
	private static function get_customer_order_connection( $resolver, $customer ) {
		// If not "billing email" or "ID" set bail early by returning an empty connection.
		if ( empty( $customer->get_billing_email() ) && empty( $customer->get_id() ) ) {
			return [
				'nodes' => [],
				'edges' => [],
			];
		}

		$customer_id   = $customer->get_id();
		$billing_email = $customer->get_billing_email();
		if ( ! empty( $customer_id ) ) {
			$resolver->set_query_arg( 'customer_id', $customer_id );
			$resolver->set_should_execute( \WC()->customer->get_id() === $customer_id );
		} elseif ( ! empty( $billing_email ) ) {
			$resolver->set_query_arg( 'billing_email', $billing_email );
			$resolver->set_should_execute( \WC()->customer->get_billing_email() === $billing_email );
		}

		return $resolver->get_connection();
	}

	/**
	 * Returns refund connection filter by customer.
	 *
	 * @param Order_Connection_Resolver $resolver  Connection resolver.
	 * @param \WC_Customer              $customer  Customer object of querying user.
	 *
	 * @return array
	 */
	private static function get_customer_refund_connection( $resolver, $customer ) {
		$empty_results = [
			'pageInfo' => null,
			'nodes'    => [],
			'edges'    => [],
		];
		// If not "billing email" or "ID" set bail early by returning an empty connection.
		if ( empty( $customer->get_billing_email() ) && empty( $customer->get_id() ) ) {
			return $empty_results;
		}

		$order_ids     = [];
		$customer_id   = $customer->get_id();
		$billing_email = $customer->get_billing_email();
		if ( ! empty( $customer_id ) ) {
			$args                     = [
				'customer_id' => $customer_id,
				'return'      => 'ids',
			];
			$order_ids_by_customer_id = wc_get_orders( $args );

			if ( is_array( $order_ids_by_customer_id ) ) {
				$order_ids = $order_ids_by_customer_id;
			}
		}

		if ( ! empty( $billing_email ) ) {
			$args               = [
				'billing_email' => $billing_email,
				'return'        => 'ids',
			];
			$order_ids_by_email = wc_get_orders( $args );
			// Merge the arrays of order IDs.
			if ( is_array( $order_ids_by_email ) ) {
				$order_ids = array_merge( $order_ids, $order_ids_by_email );
			}
		}

		// If no orders found, return empty connection.
		if ( empty( $order_ids ) ) {
			return $empty_results;
		}

		// Remove duplicates.
		$order_ids = array_unique( $order_ids );

		// Set connection args.
		$resolver->set_should_execute(
			( 0 !== $customer_id && \WC()->customer->get_id() === $customer_id )
				|| \WC()->customer->get_billing_email() === $billing_email
		);
		$resolver->set_query_arg( 'post_parent__in', array_map( 'absint', $order_ids ) );

		// Execute and return connection.
		return $resolver->get_connection();
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults.
	 *
	 * @param array  $args       Connection configuration.
	 * @param string $post_type  Connection resolving post-type.
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = [], $post_type = 'shop_order' ): array {
		// Get Post type object for use in connection resolve function.
		/**
		 * Get connection post type.
		 *
		 * @var \WP_Post_Type $post_object
		 */
		$post_object = get_post_type_object( $post_type );

		return array_merge(
			[
				'fromType'       => 'RootQuery',
				'toType'         => 'Order',
				'fromFieldName'  => 'orders',
				'connectionArgs' => self::get_connection_args( 'private' ),
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $post_object ) {
					// Check if user shop manager.
					$not_manager = ! current_user_can( $post_object->cap->edit_posts );

					// Remove any arguments that require querying user to have "shop manager" role.
					$args = $not_manager && 'shop_order' === $post_object->name
						? \array_intersect_key( $args, array_keys( self::get_connection_args( 'public' ) ) )
						: $args;

					// Initialize connection resolver.
					$resolver = new Order_Connection_Resolver( $source, $args, $context, $info, $post_object->name );

					/**
					 * If not shop manager, restrict results to orders/refunds owned by querying user
					 * and return the connection.
					 */
					if ( $not_manager ) {
						return 'shop_order_refund' === $post_object->name
							? self::get_customer_refund_connection( $resolver, \WC()->customer )
							: self::get_customer_order_connection( $resolver, \WC()->customer );
					}

					return $resolver->get_connection();
				},
			],
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @param string $access Connection argument access-level.
	 * @return array
	 */
	public static function get_connection_args( $access = 'public' ): array {
		switch ( $access ) {
			case 'private':
				return array_merge(
					get_wc_cpt_connection_args(),
					[
						'statuses'    => [
							'type'        => [ 'list_of' => 'OrderStatusEnum' ],
							'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
						],
						'customerId'  => [
							'type'        => 'Int',
							'description' => __( 'Limit result set to orders assigned a specific customer.', 'wp-graphql-woocommerce' ),
						],
						'customersIn' => [
							'type'        => [ 'list_of' => 'Int' ],
							'description' => __( 'Limit result set to orders assigned a specific group of customers.', 'wp-graphql-woocommerce' ),
						],
						'productId'   => [
							'type'        => 'Int',
							'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
						],
						'orderby'     => [
							'type'        => [ 'list_of' => 'OrdersOrderbyInput' ],
							'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
						],
					]
				);

			case 'public':
			default:
				return [
					'statuses'  => [
						'type'        => [ 'list_of' => 'OrderStatusEnum' ],
						'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
					],
					'productId' => [
						'type'        => 'Int',
						'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
					],
					'orderby'   => [
						'type'        => [ 'list_of' => 'OrdersOrderbyInput' ],
						'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
					],
					'search'    => [
						'type'        => 'String',
						'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
					],
					'dateQuery' => [
						'type'        => 'DateQueryInput',
						'description' => __( 'Filter the connection based on dates.', 'wp-graphql-woocommerce' ),
					],
				];
		}//end switch
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_refund_connection_args(): array {
		return array_merge(
			get_wc_cpt_connection_args(),
			[
				'statuses' => [
					'type'        => [ 'list_of' => 'String' ],
					'description' => __( 'Limit result set to refunds assigned a specific status.', 'wp-graphql-woocommerce' ),
				],
				'orderIn'  => [
					'type'        => [ 'list_of' => 'Int' ],
					'description' => __( 'Limit result set to refunds from a specific group of order IDs.', 'wp-graphql-woocommerce' ),
				],
			]
		);
	}
}
