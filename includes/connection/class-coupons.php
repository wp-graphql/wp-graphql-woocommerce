<?php
/**
 * Connection - Coupons
 *
 * Registers connections to Coupon
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

/**
 * Class - Coupons
 */
class Coupons {

	/**
	 * Registers the various connections from other Types to Coupon
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = [] ): array {
		return array_merge(
			[
				'fromType'       => 'RootQuery',
				'toType'         => 'Coupon',
				'fromFieldName'  => 'coupons',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function ( $source, $args, $context, $info ) {
					$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'shop_coupon' );

					if ( ! self::should_execute() ) {
						return [
							'edges' => [],
							'nodes' => [],
						];
					}

					return $resolver->get_connection();
				},
			],
			$args
		);
	}

	/**
	 * Confirms the uses has the privileges to query Coupons
	 *
	 * @return bool
	 */
	public static function should_execute() {
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
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		return array_merge(
			get_wc_cpt_connection_args(),
			[
				'code' => [
					'type'        => 'String',
					'description' => __( 'Limit result set to resources with a specific code.', 'wp-graphql-woocommerce' ),
				],
			]
		);
	}

	/**
	 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
	 * from a GraphQL Query to the WP_Query
	 *
	 * @param array              $query_args The mapped query arguments.
	 * @param array              $where_args       Query "where" args.
	 * @param mixed              $source     The query results for a query calling this.
	 * @param array              $args   All of the arguments for the query (not just the "where" args).
	 * @param AppContext         $context    The AppContext object.
	 * @param ResolveInfo        $info       The ResolveInfo object.
	 * @param mixed|string|array $post_type  The post type for the query.
	 *
	 * @return array Query arguments.
	 */
	public static function map_input_fields_to_wp_query( $query_args, $where_args, $source, $args, $context, $info, $post_type ) {
		$not_coupon_query = is_string( $post_type )
			? 'shop_coupon' !== $post_type
			: ! in_array( 'shop_coupon', $post_type, true );
		if ( $not_coupon_query ) {
			return $query_args;
		}

		$query_args = array_merge(
			$query_args,
			map_shared_input_fields_to_wp_query( $where_args )
		);

		if ( ! empty( $where_args['code'] ) ) {
			$id                     = \wc_get_coupon_id_by_code( $where_args['code'] );
			$ids                    = $id ? [ $id ] : [ '0' ];
			$query_args['post__in'] = ! empty( $query_args['post__in'] )
				? array_intersect( $ids, $query_args['post__in'] )
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
		 * @param AppContext  $context    The AppContext object
		 * @param ResolveInfo $info       The ResolveInfo object
		 */
		$query_args = apply_filters(
			'graphql_map_input_fields_to_coupon_query',
			$query_args,
			$where_args,
			$source,
			$args,
			$context,
			$info
		);

		return $query_args;
	}
}
