<?php
/**
 * ConnectionResolver - Order_Connection_Resolver
 *
 * Resolves connections to Orders
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Extension\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;

/**
 * Class Order_Connection_Resolver
 */
class Order_Connection_Resolver extends AbstractConnectionResolver {
	use WC_Connection_Resolver {
		sanitize_input_fields as sanitize_shared_input_fields;
	}

	/**
	 * The name of the post type, or array of post types the connection resolver is resolving for
	 *
	 * @var string|array
	 */
	protected $post_type;

	/**
	 * Refund_Connection_Resolver constructor.
	 *
	 * @param mixed       $source    The object passed down from the previous level in the Resolve tree.
	 * @param array       $args      The input arguments for the query.
	 * @param AppContext  $context   The context of the request.
	 * @param ResolveInfo $info      The resolve info passed down the Resolve tree.
	 */
	public function __construct( $source, $args, $context, $info ) {
		/**
		 * Set the post type for the resolver
		 */
		$this->post_type = 'shop_order';
		/**
		 * Call the parent construct to setup class data
		 */
		parent::__construct( $source, $args, $context, $info );
	}

	/**
	 * Confirms the uses has the privileges to query Orders
	 *
	 * @return bool
	 */
	public function should_execute() {
		$post_type_obj = get_post_type_object( 'shop_order' );
		switch ( true ) {
			case current_user_can( $post_type_obj->cap->edit_posts ):
			case is_a( $this->source, Customer::class ) && 'orders' === $this->info->fieldName:
				return true;
			default:
				return false;
		}
	}

	/**
	 * Creates query arguments array
	 */
	public function get_query_args() {
		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		// Set the $query_args based on various defaults and primary input $args.
		$query_args = array(
			'post_type'     => 'shop_order',
			'no_rows_found' => true,
			'return'        => 'ids',
			'limit'         => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

		/**
		 * Set the graphql_cursor_offset which is used by Config::graphql_wp_query_cursor_pagination_support
		 * to filter the WP_Query to support cursor pagination
		 */
		$cursor_offset                        = $this->get_offset();
		$query_args['graphql_cursor_offset']  = $cursor_offset;
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		/**
		 * If the starting offset is not 0 sticky posts will not be queried as the automatic checks in wp-query don't
		 * trigger due to the page parameter not being set in the query_vars, fixes #732
		 */
		if ( 0 !== $cursor_offset ) {
			$query_args['ignore_sticky_posts'] = true;
		}
		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $this->args;

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = array();
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}

		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		if ( true === is_object( $this->source ) ) {
			switch ( true ) {
				case is_a( $this->source, Customer::class ):
					if ( 'orders' === $this->info->fieldName ) {
						if ( ! empty( $args['meta_query'] ) ) {
							$args['meta_query'] = array(); // WPCS: slow query ok.
						}
						$args['meta_query'][] = array(
							'key'   => '_customer_user',
							'value' => $this->source->ID,
							'type'  => 'NUMERIC',
						);
					}
					break;
			}
		}

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['orderby'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'ASC' : 'DESC';
		}

		/**
		 * Filter the $query args to allow folks to customize queries programmatically
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query
		 * @param mixed       $source     The source that's passed down the GraphQL queries
		 * @param array       $args       The inputArgs on the field
		 * @param AppContext  $context    The AppContext passed down the GraphQL tree
		 * @param ResolveInfo $info       The ResolveInfo passed down the GraphQL tree
		 */
		$query_args = apply_filters( 'graphql_order_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return \WC_Order_Query
	 */
	public function get_query() {
		return new \WC_Order_Query( $this->get_query_args() );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		return ! empty( $this->query->get_orders() ) ? $this->query->get_orders() : array();
	}

	/**
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to WP_Query
	 * friendly keys. There's probably a cleaner/more dynamic way to approach this, but
	 * this was quick. I'd be down to explore more dynamic ways to map this, but for
	 * now this gets the job done.
	 *
	 * @param array $where_args - arguments being used to filter query.
	 *
	 * @return array
	 */
	public function sanitize_input_fields( array $where_args ) {
		global $wpdb;
		$args = $this->sanitize_shared_input_fields( $where_args );

		$key_mapping = array(
			'post_parent'         => 'parent',
			'post_parent__not_in' => 'parent_exclude',
			'post__not_in'        => 'exclude',
		);

		foreach ( $key_mapping as $key => $field ) {
			if ( isset( $args[ $key ] ) ) {
				$args[ $field ] = $args[ $key ];
				unset( $args[ $key ] );
			}
		}

		if ( ! empty( $where_args['statuses'] ) ) {
			if ( 1 === count( $where_args ) ) {
				$args['status'] = $where_args['statuses'][0];
			} else {
				$args['status'] = $where_args['statuses'];
			}
		}

		if ( ! empty( $where_args['customerId'] ) ) {
			$args['customer_id'] = $where_args['customerId'];
		}

		if ( ! empty( $where_args['customersIn'] ) ) {
			$args['customer'] = $where_args['customersIn'];
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
			$args['post__in'] = ! empty( $order_ids ) ? $order_ids : array( 0 );
		}

		// Search.
		if ( ! empty( $args['s'] ) ) {
			$order_ids = wc_order_search( $args['s'] );
			if ( ! empty( $order_ids ) ) {
				unset( $args['s'] );
				$args['post__in'] = isset( $args['post__in'] )
				? array_intersect( $order_ids, $args['post__in'] )
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
		$args = apply_filters(
			'graphql_map_input_fields_to_order_query',
			$args,
			$where_args,
			$this->source,
			$this->args,
			$this->context,
			$this->info,
			$this->post_type
		);

		return $args;
	}
}
