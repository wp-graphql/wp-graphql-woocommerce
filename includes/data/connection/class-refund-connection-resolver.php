<?php
/**
 * ConnectionResolver - Refund_Connection_Resolver
 *
 * Resolves connections to Refunds
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\WooCommerce\Model\Order;
use WPGraphQL\WooCommerce\Model\Refund;

/**
 * Class Refund_Connection_Resolver
 */
class Refund_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Include CPT Loader connection common functions.
	 */
	use WC_CPT_Loader_Common;

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
		$this->post_type = 'shop_order_refund';
		/**
		 * Call the parent construct to setup class data
		 */
		parent::__construct( $source, $args, $context, $info );
	}

	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'wc_cpt';
	}

	/**
	 * Given an ID, return the model for the entity or null
	 *
	 * @param integer $id
	 *
	 * @return mixed|Refund|null
	 */
	public function get_node_by_id( $id ) {
		return $this->get_cpt_model_by_id( $id );
	}

	/**
	 * Confirms the uses has the privileges to query Refunds
	 *
	 * @return bool
	 */
	public function should_execute() {
		$post_type_obj = get_post_type_object( 'shop_order_refund' );
		switch ( true ) {
			case current_user_can( $post_type_obj->cap->edit_posts ):
			case is_a( $this->source, Order::class ) && 'refunds' === $this->info->fieldName:
			case is_a( $this->source, Customer::class ) && 'refunds' === $this->info->fieldName:
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
			'post_type'     => 'shop_order_refund',
			'no_rows_found' => true,
			'return'        => 'ids',
			'limit'         => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

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

		if ( empty( $query_args['post_status'] ) ) {
			$query_args['post_status'] = 'any';
		}

		switch ( true ) {
			case is_a( $this->source, Order::class ):
				if ( 'refunds' === $this->info->fieldName ) {
					$query_args['parent'] = $this->source->ID;
				}
				break;
			case is_a( $this->source, Customer::class ):
				if ( 'refunds' === $this->info->fieldName ) {
					$customer_orders              = \wc_get_orders(
						array(
							'customer_id'   => $this->source->ID,
							'no_rows_found' => true,
							'return'        => 'ids',
						)
					);
					$query_args['post_parent__in'] = array_map( 'absint', $customer_orders );
				}
				break;
			default:
				break;
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
		$query_args = apply_filters( 'graphql_refund_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

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
	public function get_ids() {
		return ! empty( $this->query->get_orders() ) ? $this->query->get_orders() : array();
	}

	/**
	 * Returns meta keys to be used for connection ordering.
	 *
	 * @return array
	 */
	public function ordering_meta() {
		return array();
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
		$args = $this->sanitize_common_inputs( $where_args );

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

		if ( ! empty( $where_args['orderIn'] ) ) {
			$args['post_parent__in'] = $where_args['orderIn'];
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
			'graphql_map_input_fields_to_refund_query',
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
	 * Wrapper for "WC_Connection_Functions::is_valid_post_offset()"
	 *
	 * @param integer $offset Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return $this->is_valid_post_offset( $offset );
	}
}
