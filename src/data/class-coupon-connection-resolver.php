<?php

namespace WPGraphQL\Extensions\WooCommerce\Data;

use WPGraphQL\Data\ConnectionResolver;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class Coupon_Connection_Resolver - Connects the coupons to other objects
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since 0.0.1
 */
class Coupon_Connection_Resolver extends ConnectionResolver {

	/**
	 * This prepares the $query_args for use in the connection query. This is where default $args are set, where dynamic
	 * $args from the $source get set, and where mapping the input $args to the actual $query_args occurs.
	 *
	 * @param mixed       $source
	 * @param array       $args
	 * @param AppContext  $context
	 * @param ResolveInfo $info
	 *
	 * @return mixed
	 */
	public static function get_query_args( $source, array $args, AppContext $context, ResolveInfo $info ) {
		/**
			 * Prepare for later use
			 */
		$last  = ! empty( $args['last'] ) ? $args['last'] : null;
		$first = ! empty( $args['first'] ) ? $args['first'] : null;

		/**
		 * Set the post_type for the query based on the type of post being queried
		 */
		$query_args['post_type'] = 'shop_coupon';

		/**
			 * Don't calculate the total rows, it's not needed and can be expensive
			 */
		$query_args['no_found_rows'] = true;
		/**
		 * Set the post_status to "publish" by default
		 */
		$query_args['post_status'] = 'publish';

		/**
			 * Set posts_per_page the highest value of $first and $last, with a (filterable) max of 100
			 */
		$query_args['posts_per_page'] = min( max( absint( $first ), absint( $last ), 10 ), self::get_query_amount( $source, $args, $context, $info ) ) + 1;

		/**
			 * Set the graphql_cursor_offset which is used by Config::graphql_wp_query_cursor_pagination_support
			 * to filter the WP_Query to support cursor pagination
			 */
		$query_args['graphql_cursor_offset']  = self::get_offset( $args );
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $args;

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = array();
		if ( ! empty( $args['where'] ) ) {
			$input_fields = self::sanitize_input_fields( $args['where'], $source, $args, $context, $info );
		}

		/**
			 * Merge the input_fields with the default query_args
			 */
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		/**
			 * Map the orderby inputArgs to the WP_Query
			 */
		if ( ! empty( $args['where']['orderby'] ) && is_array( $args['where']['orderby'] ) ) {
			$query_args['orderby'] = [];
			foreach ( $args['where']['orderby'] as $orderby_input ) {
				/**
				 * These orderby options should not include the order parameter.
				 */
				if ( in_array( $orderby_input['field'], [ 'post__in', 'post_name__in', 'post_parent__in' ], true ) ) {
					$query_args['orderby'] = esc_sql( $orderby_input['field'] );
				} elseif ( ! empty( $orderby_input['field'] ) ) {
					$query_args['orderby'] = [
						esc_sql( $orderby_input['field'] ) => esc_sql( $orderby_input['order'] ),
					];
				}
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
		$query_args = apply_filters( 'graphql_coupon_connection_query_args', $query_args, $source, $args, $context, $info );
		return $query_args;
	}

	/**
	 *
	 * @param $query_args
	 *
	 * @return \WP_Query
	 */
	public static function get_query( $query_args ) {
		$query = new \WP_Query( $query_args );
		return $query;
	}

	/**
	 * Maps queried items to \WC_Coupon
	 */
	public static function query_info_filter( $query_info, $query ) {
		if ( ! empty( $query->query ) ) {
			if ( 'shop_coupon' === $query->query['post_type'] ) {
				foreach ( $query_info['items'] as &$item ) {
					$item = new \WC_Coupon( $item->ID );
				}
			}
		}

		return $query_info;
	}

	/**
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to
	 * WP_Query friendly keys.
	 *
	 * There's probably a cleaner/more dynamic way to approach this, but this was quick. I'd be
	 * down to explore more dynamic ways to map this, but for now this gets the job done.
	 *
	 * @param array       $args     The array of query arguments
	 * @param mixed       $source   The query results
	 * @param array       $all_args Array of all of the original arguments (not just the "where"
	 *                              args)
	 * @param AppContext  $context  The AppContext object
	 * @param ResolveInfo $info     The ResolveInfo object for the query
	 *
	 * @access private
	 * @return array
	 */
	public static function sanitize_input_fields( array $args, $source, array $all_args, AppContext $context, ResolveInfo $info ) {
		$arg_mapping = array( 'code' => 'title' );

		/**
		 * Map and sanitize the input args to the WP_Comment_Query compatible args
		 */
		$query_args = Types::map_input( $args, $arg_mapping );

		/**
		 * Filter the input fields
		 *
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the get_terms query
		 */
		$query_args = apply_filters( 'graphql_map_input_fields_to_coupon_wp_query', $query_args, $args, $source, $all_args, $context, $info );
		return ! empty( $query_args ) && is_array( $query_args ) ? $query_args : array();
	}
}
