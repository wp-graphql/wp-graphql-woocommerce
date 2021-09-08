<?php
/**
 * Connection - Orders
 *
 * Registers connections to Order
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

/**
 * Class - Orders
 */
class Orders {

	/**
	 * Registers the various connections from other Types to Customer
	 */
	public static function register_connections() {
		// From RootQuery To Orders.
		register_graphql_connection( self::get_connection_config() );

		// From Customer To Orders.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Customer',
					'fromFieldName' => 'orders',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'shop_order' );

						return self::get_customer_order_connection( $resolver, $source );
					},
				)
			)
		);

		// From RootQuery To Refunds.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'toType'         => 'Refund',
					'fromFieldName'  => 'refunds',
					'connectionArgs' => self::get_refund_connection_args(),
				),
				'shop_order_refund'
			)
		);
		// From Order To Refunds.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'       => 'Order',
					'toType'         => 'Refund',
					'fromFieldName'  => 'refunds',
					'connectionArgs' => self::get_refund_connection_args(),
					'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'shop_order_refund' );

						$resolver->set_query_arg( 'post_parent', $source->ID );

						return $resolver->get_connection();
					},
				),
				'shop_order_refund'
			)
		);
		// From Customer To Refunds.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'       => 'Customer',
					'toType'         => 'Refund',
					'fromFieldName'  => 'refunds',
					'connectionArgs' => self::get_refund_connection_args(),
					'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'shop_order_refund' );

						$customer_orders = \wc_get_orders(
							array(
								'customer_id'   => $source->ID,
								'no_rows_found' => true,
								'return'        => 'ids',
							)
						);

						$resolver->set_query_arg( 'post_parent__in', array_map( 'absint', $customer_orders ) );

						return $resolver->get_connection();
					},
				),
				'shop_order_refund'
			)
		);
	}

	/**
	 * Returns order connection filter by customer.
	 *
	 * @param PostObjectConnectionResolver                       $resolver  Connection resolver.
	 * @param \WC_Customer|\WPGraphQL\WooCommerce\Model\Customer $customer  Customer object of querying user.
	 *
	 * @return array
	 */
	private static function get_customer_order_connection( $resolver, $customer ) {
		// If not "billing email" or "ID" set bail early by returning an empty connection.
		if ( empty( $customer->get_billing_email() ) && empty( $customer->get_id() ) ) {
			return array();
		}

		// If the querying user has a "billing email" set filter orders by user's billing email, otherwise filter by user's ID.
		$meta_key   = ! empty( $customer->get_billing_email() ) ? '_billing_email' : '_customer_user';
		$meta_value = ! empty( $customer->get_billing_email() )
			? $customer->get_billing_email()
			: $customer->get_id();
		$resolver->set_query_arg( 'meta_key', $meta_key );
		$resolver->set_query_arg( 'meta_value', $meta_value );

		return $resolver->get_connection();
	}

	/**
	 * Returns refund connection filter by customer.
	 *
	 * @param PostObjectConnectionResolver                       $resolver  Connection resolver.
	 * @param \WC_Customer|\WPGraphQL\WooCommerce\Model\Customer $customer  Customer object of querying user.
	 *
	 * @return array
	 */
	private static function get_customer_refund_connection( $resolver, $customer ) {
		// If not "billing email" or "ID" set bail early by returning an empty connection.
		if ( empty( $customer->get_billing_email() ) && empty( $customer->get_id() ) ) {
			return array(
				'pageInfo' => null,
				'nodes'    => array(),
				'edges'    => array(),
			);
		}
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
	public static function get_connection_config( $args = array(), $post_type = 'shop_order' ): array {
		// Get Post type object for use in connection resolve function.
		$post_object = get_post_type_object( $post_type );

		return array_merge(
			array(
				'fromType'       => 'RootQuery',
				'toType'         => 'Order',
				'fromFieldName'  => 'orders',
				'connectionArgs' => self::get_connection_args( 'private' ),
				'queryClass'     => '\WC_Order_Query',
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $post_object ) {
					// Check if user shop manager.
					$not_manager = ! current_user_can( $post_object->cap->edit_posts );

					// Remove any arguments that require querying user to have "shop manager" role.
					$args = $not_manager && 'shop_order' === $post_object->name
						? \array_intersect_key( $args, array_keys( self::get_connection_args( 'public' ) ) )
						: $args;

					// Initialize connection resolver.
					$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, $post_object->name );

					/**
					 * If not shop manager, restrict results to orders/refunds owned by querying user
					 * and return the connection.
					 */
					if ( 'shop_order_refund' === $post_object->name ) {
						$empty_results = array(
							'pageInfo' => null,
							'nodes'    => array(),
							'edges'    => array(),
						);

						return $not_manager
							? $empty_results
							: $resolver->get_connection();
					}

					return $not_manager
						? self::get_customer_order_connection( $resolver, \WC()->customer )
						: $resolver->get_connection();
				},
			),
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
					array(
						'statuses'    => array(
							'type'        => array( 'list_of' => 'OrderStatusEnum' ),
							'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
						),
						'customerId'  => array(
							'type'        => 'Int',
							'description' => __( 'Limit result set to orders assigned a specific customer.', 'wp-graphql-woocommerce' ),
						),
						'customersIn' => array(
							'type'        => array( 'list_of' => 'Int' ),
							'description' => __( 'Limit result set to orders assigned a specific group of customers.', 'wp-graphql-woocommerce' ),
						),
						'productId'   => array(
							'type'        => 'Int',
							'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
						),
						'orderby'     => array(
							'type'        => array( 'list_of' => 'OrdersOrderbyInput' ),
							'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
						),
					)
				);

			case 'public':
			default:
				return array(
					'statuses'  => array(
						'type'        => array( 'list_of' => 'OrderStatusEnum' ),
						'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
					),
					'productId' => array(
						'type'        => 'Int',
						'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
					),
					'orderby'   => array(
						'type'        => array( 'list_of' => 'OrdersOrderbyInput' ),
						'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
					),
					'search'    => array(
						'type'        => 'String',
						'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
					),
					'dateQuery' => array(
						'type'        => 'DateQueryInput',
						'description' => __( 'Filter the connection based on dates.', 'wp-graphql-woocommerce' ),
					),
				);
		}
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_refund_connection_args(): array {
		return array_merge(
			get_wc_cpt_connection_args(),
			array(
				'statuses' => array(
					'type'        => array( 'list_of' => 'String' ),
					'description' => __( 'Limit result set to refunds assigned a specific status.', 'wp-graphql-woocommerce' ),
				),
				'orderIn'  => array(
					'type'        => array( 'list_of' => 'Int' ),
					'description' => __( 'Limit result set to refunds from a specific group of order IDs.', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Filter the $query_args to allow folks to customize queries programmatically
	 *
	 * @param array       $query_args  The args that will be passed to the WP_Query.
	 * @param mixed       $source      The source that's passed down the GraphQL queries.
	 * @param array       $args        The inputArgs on the field.
	 * @param AppContext  $context     The AppContext passed down the GraphQL tree.
	 * @param ResolveInfo $info        The ResolveInfo passed down the GraphQL tree.
	 *
	 * @return array
	 */
	public static function post_object_connection_query_args( $query_args, $source, $args, $context, $info ) {
		$post_types      = array( 'shop_order', 'shop_order_refund' );
		$not_order_query = is_string( $query_args['post_type'] )
			? empty( array_intersect( $post_types, array( $query_args['post_type'] ) ) )
			: empty( array_intersect( $post_types, $query_args['post_type'] ) );

		if ( $not_order_query ) {
			return $query_args;
		}

		$query_args['type'] = \wc_get_order_types( 'view-orders' );

		if ( empty( $args['where'] ) || empty( $args['where']['statuses'] ) ) {
			$query_args['post_status'] = array_keys( \wc_get_order_statuses() );
		}

		return $query_args;
	}

	/**
	 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
	 * from a GraphQL Query to the WP_Query
	 *
	 * @param array              $query_args The mapped query arguments.
	 * @param array              $where_args Query "where" args.
	 * @param mixed              $source     The query results for a query calling this.
	 * @param array              $args       All of the arguments for the query (not just the "where" args).
	 * @param AppContext         $context    The AppContext object.
	 * @param ResolveInfo        $info       The ResolveInfo object.
	 * @param mixed|string|array $post_type  The post type for the query.
	 *
	 * @return array Query arguments.
	 */
	public static function map_input_fields_to_wp_query( $query_args, $where_args, $source, $args, $context, $info, $post_type ) {
		$post_types = array( 'shop_order', 'shop_order_refund' );
		if ( empty( array_intersect( $post_types, is_string( $post_type ) ? array( $post_type ) : $post_type ) ) ) {
			return $query_args;
		}

		global $wpdb;

		$query_args = array_merge(
			$query_args,
			map_shared_input_fields_to_wp_query( $where_args )
		);

		// Process order meta inputs.
		$metas      = array( 'customerId', 'customersIn' );
		$meta_query = array();
		foreach ( $metas as $field ) {
			if ( isset( $query_args[ $field ] ) ) {
				$value = $query_args[ $field ];
				switch ( $field ) {
					case 'customerId':
					case 'customersIn':
						if ( is_null( $value ) ) {
							$meta_query[] = array(
								'key'     => '_customer_user',
								'value'   => 0,
								'compare' => '=',
							);
						} else {
							$meta_query[] = array(
								'key'     => '_customer_user',
								'value'   => $value,
								'compare' => is_array( $value ) ? 'IN' : '=',
							);
						}
				}
			}
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query; // WPCS: slow query ok.
		}

		$key_mapping = array(
			'statuses' => 'post_status',
			'orderIn'  => 'post_parent__in',
		);

		$prefixer = function( $status ) {
			$statuses = array_keys( \wc_get_order_statuses() );

			if ( in_array( "wc-{$status}", $statuses, true ) ) {
				return "wc-{$status}";
			}

			return $status;
		};

		foreach ( $key_mapping as $key => $field ) {
			if ( isset( $query_args[ $key ] ) ) {
				$query_args[ $field ] = 'statuses' === $key
					? array_map( $prefixer, $query_args[ $key ] )
					: $query_args[ $key ];
				unset( $query_args[ $key ] );
			}
		}

		// @codingStandardsIgnoreStart
		// if ( ! empty( $where_args['statuses'] ) ) {
		// 	if ( 1 === count( $where_args ) ) {
		// 		$query_args['status'] = $where_args['statuses'][0];
		// 	} else {
		// 		$query_args['status'] = $where_args['statuses'];
		// 	}
		// }
		// @codingStandardsIgnoreEnd

		// Search by product.
		if ( ! empty( $where_args['productId'] ) ) {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT order_id
					FROM {$wpdb->prefix}woocommerce_order_items
					WHERE order_item_id IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND meta_value = %d )
					AND order_item_type = 'line_item'",
					absint( $where_args['productId'] )
				)
			);

			// Force WP_Query return empty if don't found any order.
			$query_args['post__in'] = ! empty( $order_ids ) ? $order_ids : array( 0 );
		}

		// Search.
		if ( ! empty( $query_args['s'] ) ) {
			$order_ids = wc_order_search( $query_args['s'] );
			if ( ! empty( $order_ids ) ) {
				unset( $query_args['s'] );
				$query_args['post__in'] = isset( $query_args['post__in'] )
				? array_intersect( $order_ids, $query_args['post__in'] )
				: $order_ids;
			}
		}

		/**
		 * Filter the input fields
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the WP_Query
		 *
		 * @param array       $args       The mapped query arguments
		 * @param array       $where_args Query "where" args
		 * @param mixed       $source     The query results for a query calling this
		 * @param array       $all_args   All of the arguments for the query (not just the "where" args)
		 * @param AppContext  $context    The AppContext object
		 * @param ResolveInfo $info       The ResolveInfo object
		 * @param mixed|string|array      $post_type  The post type for the query
		 */
		$query_args = apply_filters(
			'graphql_map_input_fields_to_order_query',
			$query_args,
			$where_args,
			$source,
			$args,
			$context,
			$info,
			$post_type
		);

		return $query_args;
	}
}
