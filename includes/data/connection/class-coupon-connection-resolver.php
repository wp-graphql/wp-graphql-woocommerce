<?php
/**
 * ConnectionResolver - Coupon_Connection_Resolver
 *
 * Resolves connections to Coupons
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Error\InvariantViolation;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * Class Coupon_Connection_Resolver
 *
 * @deprecated v0.10.0
 *
 * @property \WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader $loader
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 */
class Coupon_Connection_Resolver extends AbstractConnectionResolver {
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
	 * @param mixed                                $source    The object passed down from the previous level in the Resolve tree.
	 * @param array                                $args      The input arguments for the query.
	 * @param \WPGraphQL\AppContext                $context   The context of the request.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info      The resolve info passed down the Resolve tree.
	 */
	public function __construct( $source, $args, $context, $info ) {
		/**
		 * Set the post type for the resolver
		 */
		$this->post_type = 'shop_coupon';

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
		return 'wc_post';
	}

	/**
	 * Given an ID, return the model for the entity or null
	 *
	 * @param integer $id Node ID.
	 *
	 * @return mixed|\WPGraphQL\WooCommerce\Model\Coupon|null
	 */
	public function get_node_by_id( $id ) {
		return $this->get_cpt_model_by_id( $id );
	}

	/**
	 * Confirms the uses has the privileges to query Coupons
	 *
	 * @return bool
	 */
	public function should_execute() {
		/**
		 * Get coupon post type.
		 *
		 * @var \WP_Post_Type $post_type_obj
		 */
		$post_type_obj = get_post_type_object( 'shop_coupon' );
		switch ( true ) {
			case current_user_can( $post_type_obj->cap->edit_posts ):
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
		$query_args = [
			'post_type'      => 'shop_coupon',
			'post_status'    => 'any',
			'perm'           => 'readable',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
			'post_parent'    => 0,
		];

		/**
		 * Set the cursor args.
		 *
		 * @see \WPGraphQL\Data\Config::graphql_wp_query_cursor_pagination_support
		 */
		$query_args['graphql_after_cursor']   = $this->get_after_offset();
		$query_args['graphql_before_cursor']  = $this->get_before_offset();
		$query_args['graphql_cursor_compare'] = ! empty( $last ) ? '>' : '<';

		/**
		 * If the starting offset is not 0 sticky posts will not be queried as the automatic checks in wp-query don't
		 * trigger due to the page parameter not being set in the query_vars, fixes #732
		 */
		if ( empty( $query_args['graphql_after_cursor'] ) && empty( $query_args['graphql_before_cursor'] ) ) {
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
			$query_args['order'] = ! empty( $last ) ? 'ASC' : 'DESC';
		}

		/**
		 * Filter the $query args to allow folks to customize queries programmatically
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query
		 * @param mixed       $source     The source that's passed down the GraphQL queries
		 * @param array       $args       The inputArgs on the field
		 * @param \WPGraphQL\AppContext  $context    The AppContext passed down the GraphQL tree
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo passed down the GraphQL tree
		 */
		$query_args = apply_filters( 'graphql_coupon_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @throws \GraphQL\Error\InvariantViolation Filtering suppressed.
	 *
	 * @return \WP_Query
	 */
	public function get_query() {
		$query = new \WP_Query( $this->query_args );

		if ( isset( $query->query_vars['suppress_filters'] ) && true === $query->query_vars['suppress_filters'] ) {
			throw new InvariantViolation( __( 'WP_Query has been modified by a plugin or theme to suppress_filters, which will cause issues with WPGraphQL Execution. If you need to suppress filters for a specific reason within GraphQL, consider registering a custom field to the WPGraphQL Schema with a custom resolver.', 'wp-graphql-woocommerce' ) );
		}

		return $query;
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids() {
		return ! empty( $this->query->posts ) ? $this->query->posts : [];
	}

	/**
	 * Returns meta keys to be used for connection ordering.
	 *
	 * @return array
	 */
	public function ordering_meta() {
		return [];
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

		if ( ! empty( $where_args['code'] ) ) {
			$id               = \wc_get_coupon_id_by_code( $where_args['code'] );
			$ids              = $id ? [ $id ] : [ '0' ];
			$args['post__in'] = isset( $args['post__in'] )
			? array_intersect( $ids, $args['post__in'] )
			: $ids;
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
		 * @param \WPGraphQL\AppContext  $context    The AppContext object
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo object
		 * @param mixed|string|array      $post_type  The post type for the query
		 */
		$args = apply_filters(
			'graphql_map_input_fields_to_coupon_query',
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
