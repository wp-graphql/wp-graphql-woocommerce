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
		add_filter(
			'graphql_post_object_connection_query_args',
			array( __CLASS__, 'post_object_connection_query_args' ),
			10,
			5
		);
		add_filter(
			'graphql_map_input_fields_to_wp_query',
			array( __CLASS__, 'map_input_fields_to_wp_query' ),
			10,
			7
		);

		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );

		// From Customer.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Customer',
					'fromFieldName' => 'orders',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'shop_order' );

						return $resolver->get_connection();
					},
				)
			)
		);
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		return array_merge(
			array(
				'fromType'       => 'RootQuery',
				'toType'         => 'Order',
				'fromFieldName'  => 'orders',
				'connectionArgs' => self::get_connection_args( 'private' ),
					'resolve'        => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$post_type_obj = get_post_type_object( 'shop_order' );
						$not_manager   = ! current_user_can( $post_type_obj->cap->edit_posts );

						if ( $not_manager ) {
							$public_args = array_keys( self::get_connection_args( 'public' ) );
							$args = array_filter(
								$args,
								function( $key ) use ( $public_args ) {
									return in_array( $key, $public_args, true );
								},
								ARRAY_FILTER_USE_KEY
							);
						}

						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'shop_order' );

						$customer_id = get_current_user_id();
						if ( $not_manager && 0 !== $customer_id ) {
							$resolver->set_query_arg( 'customer_id', $customer_id );
						}

						return $resolver->get_connection();
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
		if ( $source instanceof \WPGraphQL\WooCommerce\Model\Customer ) {
			$meta_query = array(
				array(
					'key'     => '_customer_user',
					'value'   => $source->ID,
					'compare' => '=',
				)
			);

			if ( ! empty( $query_args['meta_query'] ) ) {
				array_push( $query_args['meta_query'], $meta_query );
			} else {
				$query_args['meta_query'] = $meta_query;
			}
		}

		if ( empty( $args['where'] ) || empty( $args['where']['statuses'] ) ) {
			$query_args['post_status'] = array_keys( \wc_get_order_statuses() );
		}

		$query_args['type'] = \wc_get_order_types( 'view-orders' );

		//wp_send_json( $query_args );

		return $query_args;
	}

	/**
	 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
	 * from a GraphQL Query to the WP_Query
	 *
	 * @param array              $query_args The mapped query arguments.
	 * @param array              $args       Query "where" args.
	 * @param mixed              $source     The query results for a query calling this.
	 * @param array              $all_args   All of the arguments for the query (not just the "where" args).
	 * @param AppContext         $context    The AppContext object.
	 * @param ResolveInfo        $info       The ResolveInfo object.
	 * @param mixed|string|array $post_type  The post type for the query.
	 *
	 * @return array Query arguments.
	 */
	public static function map_input_fields_to_wp_query( $query_args, $where_args, $source, $args, $context, $info, $post_type ) {
		if ( ! in_array( 'shop_order', $post_type, true ) ) {
			return $query_args;
		}

		global $wpdb;

		$query_args = array_merge(
			$query_args,
			map_shared_input_fields_to_wp_query( $where_args ),
		);

		// Process order meta inputs.
		$metas      = array( 'customerId', 'customersIn' );
		$meta_query = array();
		foreach( $metas as $field ) {
			if ( isset( $query_args[ $field ] ) ) {
				$value = $query_args[ $field ];
				switch( $field ) {
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
			'statuses'            => 'post_status',
			'post_parent'         => 'parent',
			'post_parent__not_in' => 'parent_exclude',
			'post__not_in'        => 'exclude',
		);

		$prefixer = function( $status ) {
			return "wc-{$status}";
		};

		foreach ( $key_mapping as $key => $field ) {
			if ( isset( $query_args[ $key ] ) ) {
				$query_args[ $field ] = 'statuses' === $key
					? array_map( $prefixer, $query_args[ $key ] )
					: $query_args[ $key ];
				unset( $query_args[ $key ] );
			}
		}

		if ( ! empty( $where_args['statuses'] ) ) {
			if ( 1 === count( $where_args ) ) {
				$query_args['status'] = $where_args['statuses'][0];
			} else {
				$query_args['status'] = $where_args['statuses'];
			}
		}

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
