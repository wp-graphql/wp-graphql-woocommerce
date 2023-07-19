<?php
/**
 * Connection type - Customers
 *
 * Registers connections to Customers
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\UserConnectionResolver;
use WPGraphQL\WooCommerce\Model\Customer;

/**
 * Class - Customers
 */
class Customers {
	/**
	 * Registers the various connections from other Types to Customer
	 *
	 * @return void
	 */
	public static function register_connections() {
		register_graphql_connection(
			[
				'fromType'       => 'RootQuery',
				'toType'         => 'Customer',
				'fromFieldName'  => 'customers',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new UserConnectionResolver( $source, $args, $context, $info );

					if ( ! self::should_execute() ) {
						return [
							'nodes' => [],
							'edges' => [],
						];
					}

					$resolver->set_query_arg( 'role', 'customer' );

					return $resolver->get_connection();
				},
			]
		);

		register_graphql_connection(
			[
				'fromType'       => 'Coupon',
				'toType'         => 'Customer',
				'fromFieldName'  => 'usedBy',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new UserConnectionResolver( $source, $args, $context, $info );

					$resolver->set_query_arg( 'include', $source->used_by_ids );
					$resolver->set_query_arg( 'role', 'customer' );

					if ( ! self::should_execute() ) {
						return [
							'nodes' => [],
							'edges' => [],
						];
					}

					return $resolver->get_connection();
				},
			]
		);
	}

	/**
	 * Confirms the uses has the privileges to query Customer
	 *
	 * @return bool
	 */
	public static function should_execute() {
		switch ( true ) {
			case current_user_can( 'list_users' ):
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
		return [
			'search'  => [
				'type'        => 'String',
				'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
			],
			'exclude' => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => __( 'Ensure result set excludes specific IDs.', 'wp-graphql-woocommerce' ),
			],
			'include' => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => __( 'Limit result set to specific ids.', 'wp-graphql-woocommerce' ),
			],
			'email'   => [
				'type'        => 'String',
				'description' => __( 'Limit result set to resources with a specific email.', 'wp-graphql-woocommerce' ),
			],
			'orderby' => [
				'type'        => 'CustomerConnectionOrderbyEnum',
				'description' => __( 'Order results by a specific field.', 'wp-graphql-woocommerce' ),
			],
			'order'   => [
				'type'        => 'OrderEnum',
				'description' => __( 'Order of results.', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
	 * from a GraphQL Query to the WP_Query
	 *
	 * @param array                                $query_args  The mapped query arguments.
	 * @param array                                $where_args  Query "where" args.
	 * @param mixed                                $source      The query results for a query calling this.
	 * @param array                                $args        All of the arguments for the query (not just the "where" args).
	 * @param \WPGraphQL\AppContext                $context     The AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info        The ResolveInfo object.
	 *
	 * @return array Query arguments.
	 */
	public static function map_input_fields_to_wp_query( $query_args, $where_args, $source, $args, $context, $info ) {
		$key_mapping = [
			'search'  => 'search',
			'exclude' => 'exclude',
			'include' => 'include',
		];

		foreach ( $key_mapping as $key => $field ) {
			if ( ! empty( $where_args[ $key ] ) ) {
				$query_args[ $field ] = $where_args[ $key ];
			}
		}

		// Filter by email.
		if ( ! empty( $where_args['email'] ) ) {
			$query_args['search']         = $where_args['email'];
			$query_args['search_columns'] = [ 'user_email' ];
		}

		/**
		 * Map the orderby inputArgs to the WP_Query
		 */
		if ( ! empty( $where_args['orderby'] ) ) {
			$query_args['orderby'] = $where_args['orderby'];
		}

		/**
		 * Map the orderby inputArgs to the WP_Query
		 */
		if ( ! empty( $where_args['order'] ) ) {
			$query_args['order'] = $where_args['order'];
		}

		/**
		 * Filter the input fields
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the WP_Query
		 *
		 * @param array                                $args       The mapped query arguments
		 * @param array                                $where_args Query "where" args
		 * @param mixed                                $source     The query results for a query calling this
		 * @param array                                $all_args   All of the arguments for the query (not just the "where" args)
		 * @param \WPGraphQL\AppContext                $context    The AppContext object
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo object
		 */
		$query_args = apply_filters(
			'graphql_map_input_fields_to_customer_query',
			$query_args,
			$where_args,
			$source,
			$args,
			$context,
			$info
		);

		return $query_args;
	}

	/**
	 * Temporary function until necessary functionality
	 * has been added to the UserConnectionResolver
	 *
	 * @param array                                                 $connection  Resolved connection.
	 * @param \WPGraphQL\Data\Connection\AbstractConnectionResolver $resolver  Resolver class.
	 *
	 * @return array
	 */
	public static function upgrade_models( $connection, $resolver ) {
		if ( 'customers' === $resolver->getInfo()->fieldName ) {
			$nodes = [];
			$edges = [];
			foreach ( $connection['nodes'] as $node ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$nodes[] = new Customer( $node->databaseId );
			}

			foreach ( $connection['edges'] as $edge ) {
				$edges[] = array_merge(
					$edge,
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					[ 'node' => new Customer( $edge['node']->databaseId ) ]
				);
			}

			$connection['nodes'] = $nodes;
			$connection['edges'] = $edges;
		}

		return $connection;
	}
}
