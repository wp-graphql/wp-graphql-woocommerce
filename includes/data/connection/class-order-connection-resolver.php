<?php
/**
 * ConnectionResolver - Order_Connection_Resolver
 *
 * Resolves connections to Orders
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader;
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Order_Connection_Resolver
 */
class Order_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Include CPT Loader connection common functions.
	 */
	use WC_CPT_Loader_Common;

	/**
	 * The name of the post type, or array of post types the connection resolver is resolving for
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * This stores the should
	 *
	 * @var boolean
	 */
	protected $should_execute = false;

	/**
	 * Refund_Connection_Resolver constructor.
	 *
	 * @param mixed       $source    The object passed down from the previous level in the Resolve tree.
	 * @param array       $args      The input arguments for the query.
	 * @param AppContext  $context   The context of the request.
	 * @param ResolveInfo $info      The resolve info passed down the Resolve tree.
	 * @param string      $post_type The post type for the connection resolver.
	 */
	public function __construct( $source, $args, $context, $info, $post_type = 'shop_order' ) {
		/**
		 * Set the post type for the resolver.
		 */
		$this->post_type = $post_type;

		/**
		 * Call the parent construct to setup class data.
		 */
		parent::__construct( $source, $args, $context, $info );

		/**
		 * Default to true.
		 */
		$this->should_execute = true;
	}

	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'wc_post';
	}

	/**
	 * Given an ID, return the model for the entity or null
	 *
	 * @param integer $id  Node ID.
	 *
	 * @return mixed|Order|null
	 */
	public function get_node_by_id( $id ) {
		return $this->get_cpt_model_by_id( $id );
	}

	/**
	 * Checks if user is authorized to query orders
	 *
	 * @return bool
	 */
	public function should_execute() {
		/**
		 * Get order post type.
		 *
		 * @var \WP_Post_Type $post_type_obj
		 */
		$post_type_obj = get_post_type_object( $this->post_type );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			return true;
		} elseif ( isset( $this->query_args['customer_id'], $this->source ) && $this->source->ID === $this->query_args['customer_id'] ) {
			return true;
		} elseif ( isset( $this->query_args['billing_email'], $this->source ) && $this->source->email === $this->query_args['billing_email'] ) {
			return true;
		}

		return $this->should_execute;
	}

	/**
	 * Sets whether or not the query should execute
	 *
	 * @param bool $should_execute Whether or not the query should execute.
	 *
	 * @return void
	 */
	public function set_should_execute( bool $should_execute ) {
		$this->should_execute = $should_execute;
	}

	/**
	 * Creates query arguments array
	 *
	 * @return array
	 */
	public function get_query_args() {
		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		// Set the $query_args based on various defaults and primary input $args.
		$query_args = [
			'post_type'     => $this->post_type,
			'no_rows_found' => true,
			'return'        => 'ids',
			'limit'         => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		];

		/**
		 * Set posts_per_page the highest value of $first and $last, with a (filterable) max of 100
		 */
		$query_args['posts_per_page'] = $this->one_to_one ? 1 : min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1;

		/**
		 * Set the graphql cursor args.
		 */
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';
		$query_args['graphql_after_cursor']   = $this->get_after_offset();
		$query_args['graphql_before_cursor']  = $this->get_before_offset();

		/**
		 * If the cursor offsets not empty,
		 * ignore sticky posts on the query
		 */
		if ( ! empty( $this->get_after_offset() ) || ! empty( $this->get_after_offset() ) ) {
			$query_args['ignore_sticky_posts'] = true;
		}

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $this->args;

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = [];
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}

		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['orderby'] ) ) {
			$query_args['order']   = ! empty( $last ) ? 'ASC' : 'DESC';
			$query_args['orderby'] = [ 'date' => $query_args['order'] ];
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
	 * @throws InvariantViolation  Filter currently not supported for WC_Order_Query.
	 *
	 * @return \WC_Order_Query
	 */
	public function get_query() {
		$query = new \WC_Order_Query( $this->query_args );

		if ( true === $query->get( 'suppress_filters', false ) ) {
			throw new InvariantViolation( __( 'WC_Order_Query has been modified by a plugin or theme to suppress_filters, which will cause issues with WPGraphQL Execution. If you need to suppress filters for a specific reason within GraphQL, consider registering a custom field to the WPGraphQL Schema with a custom resolver.', 'wp-graphql-woocommerce' ) );
		}

		return $query;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ids_from_query() {
		$ids = ! empty( $this->query->get_orders() ) ? $this->query->get_orders() : [];

		// If we're going backwards, we need to reverse the array.
		if ( ! empty( $this->args['last'] ) ) {
			$ids = array_reverse( $ids );
		}

		return $ids;
	}

	/**
	 * Returns meta keys to be used for connection ordering.
	 *
	 * @return array
	 */
	public function ordering_meta() {
		return [
			'_order_key',
			'_cart_discount',
			'_order_total',
			'_order_tax',
			'_date_paid',
			'_date_completed',
		];
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
		$args = $this->sanitize_common_inputs( $where_args );

		$key_mapping = [
			'post_parent'         => 'parent',
			'post_parent__not_in' => 'parent_exclude',
			'post__not_in'        => 'exclude',
		];

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
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
			$args['post__in'] = ! empty( $order_ids ) ? $order_ids : [ 0 ];
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

	/**
	 * Determine whether or not the the offset is valid, i.e the order corresponding to the offset
	 * exists. Offset is equivalent to order_id. So this function is equivalent to checking if the
	 * post with the given ID exists.
	 *
	 * @param int $offset The ID of the node used in the cursor offset.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return (bool) \wc_get_order( absint( $offset ) );
	}
}
