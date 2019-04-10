<?php
/**
 * ConnectionResolver - Refund_Connection_Resolver
 *
 * Resolves connections to Refunds
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extension\WooCommerce\Model\Customer;
use WPGraphQL\Extension\WooCommerce\Model\Order;

/**
 * Class Refund_Connection_Resolver
 */
class Refund_Connection_Resolver extends AbstractConnectionResolver {
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
		$this->post_type = 'shop_order_refund';
		/**
		 * Call the parent construct to setup class data
		 */
		parent::__construct( $source, $args, $context, $info );
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
			'post_type'      => 'shop_order_refund',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

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

		if ( empty( $query_args['post_status'] ) ) {
			$query_args['post_status'] = 'any';
		}

		switch ( true ) {
			case is_a( $this->source, Order::class ):
				if ( 'refunds' === $this->info->fieldName ) {
					unset( $query_args['post_parent__in'] );
					$query_args['post_parent'] = $this->source->ID;
				}
				break;
			case is_a( $this->source, Customer::class ):
				if ( 'refunds' === $this->info->fieldName ) {
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
	 * @return \WP_Query
	 */
	public function get_query() {
		return new \WP_Query( $this->get_query_args() );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		return ! empty( $this->query->posts ) ? $this->query->posts : [];
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
		$args = $this->sanitize_shared_input_fields( $where_args );

		if ( ! empty( $where_args['statuses'] ) ) {
			$args['post_status'] = array();
			$statuses            = $this->get_order_statuses();

			foreach ( $where_args['statuses'] as $status ) {
				if ( in_array( $status, $statuses, true ) ) {
					$args['post_status'][] = 'wc-' . $status;
				} elseif ( 'any' === $status ) {
					// Set status to "any" and short-circuit out.
					$args['post_status'] = 'any';
					break;
				} else {
					$args['post_status'][] = $status;
				}
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
}
