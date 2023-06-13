<?php
/**
 * ConnectionResolver - Customer_Connection_Resolver
 *
 * Resolves connections to Customers
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\WooCommerce\Model\Coupon;

/**
 * Class Customer_Connection_Resolver
 *
 * @deprecated v0.10.0
 */
class Customer_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'wc_customer';
	}

	/**
	 * Confirms the uses has the privileges to query Customers
	 *
	 * @return bool
	 */
	public function should_execute() {
		switch ( true ) {
			case current_user_can( 'list_users' ):
				return true;
			default:
				return false;
		}
	}

	/**
	 * Creates query arguments array
	 */
	public function get_query_args() {
		/**
		 * Prepare for later use
		 */
		$last = ! empty( $this->args['last'] ) ? $this->args['last'] : null;

		/**
		 * Set the $query_args based on various defaults and primary input $args
		 */
		$query_args['count_total'] = false;
		$query_args['orderby']     = 'ID';
		$query_args['order']       = ! empty( $this->args['last'] ) ? 'ASC' : 'DESC';
		$query_args['number']      = $this->get_query_amount() + 1;

		/**
		 * Set the graphql_cursor_offset which is used by Config::graphql_wp_user_query_cursor_pagination_support
		 * to filter the WP_User_Query to support cursor pagination
		 */
		$cursor_offset                        = $this->get_offset();
		$query_args['graphql_cursor_offset']  = $cursor_offset;
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		$input_fields = [];
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		if ( true === is_object( $this->source ) ) {
			switch ( true ) {
				case is_a( $this->source, Coupon::class ):
					if ( 'usedBy' === $this->info->fieldName ) {
						$query_args['include'] = ! empty( $query_args['include'] )
							? array_merge( $query_args['include'], $this->source->used_by_ids )
							: $this->source->used_by_ids;
					}
					break;
				default:
					break;
			}
		}

		$query_args['fields'] = 'ID';

		/**
		 * Map the orderby inputArgs to the WP_User_Query
		 */
		if ( ! empty( $this->args['where']['orderby'] ) && is_array( $this->args['where']['orderby'] ) ) {
			$query_args['orderby'] = [];
			foreach ( $this->args['where']['orderby'] as $orderby_input ) {
				/**
				 * These orderby options should not include the order parameter.
				 */
				if ( in_array( $orderby_input['field'], [ 'login__in', 'nicename__in' ], true ) ) {
					$query_args['orderby'] = esc_sql( $orderby_input['field'] );
				} elseif ( ! empty( $orderby_input['field'] ) ) {
					$query_args['orderby'] = [ $orderby_input['field'] => $orderby_input['order'] ];
				}
			}
		}

		/**
		 * Convert meta_value_num to seperate meta_value value field which our
		 * graphql_wp_term_query_cursor_pagination_support knowns how to handle
		 */
		if ( isset( $query_args['orderby'] ) && 'meta_value_num' === $query_args['orderby'] ) {
			$query_args['orderby'] = [
				//phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => empty( $query_args['order'] ) ? 'DESC' : $query_args['order'],
			];
			unset( $query_args['order'] );
			$query_args['meta_type'] = 'NUMERIC';
		}
		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['orderby'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'ASC' : 'DESC';
		}

		if (
			empty( $query_args['role'] ) &&
			empty( $query_args['role__in'] ) &&
			empty( $query_args['role__not_in'] )
		) {
			$query_args['role'] = 'customer';
		}

		$query_args = apply_filters(
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			'graphql_customer_connection_query_args',
			$query_args,
			$this->source,
			$this->args,
			$this->context,
			$this->info
		);

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return \WP_User_Query
	 */
	public function get_query() {
		return new \WP_User_Query( $this->get_query_args() );
	}

	/**
	 * Returns an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids() {
		$results = $this->get_query()->get_results();
		return ! empty( $results ) ? $results : [];
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
		$args = [];

		$key_mapping = [
			'search'    => 'search',
			'exclude'   => 'exclude',
			'include'   => 'include',
			'role'      => 'role',
			'roleIn'    => 'role__in',
			'roleNotIn' => 'role__not_in',
		];

		foreach ( $key_mapping as $key => $field ) {
			if ( ! empty( $where_args[ $key ] ) ) {
				$args[ $field ] = $where_args[ $key ];
			}
		}

		// Filter by email.
		if ( ! empty( $where_args['email'] ) ) {
			$args['search']         = $where_args['email'];
			$args['search_columns'] = [ 'user_email' ];
		}

		/**
		 * Map the orderby inputArgs to the WP_Query
		 */
		if ( ! empty( $where_args['orderby'] ) ) {
			$args['orderby'] = $where_args['orderby'];
		}

		/**
		 * Map the orderby inputArgs to the WP_Query
		 */
		if ( ! empty( $where_args['order'] ) ) {
			$args['order'] = $where_args['order'];
		}

		return $args;
	}

	/**
	 * Determine whether or not the the offset is valid, i.e the user corresponding to the offset exists.
	 * Offset is equivalent to user_id. So this function is equivalent
	 * to checking if the user with the given ID exists.
	 *
	 * @param integer $offset  User ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		global $wpdb;

		if ( ! empty( wp_cache_get( $offset, 'users' ) ) ) {
			return true;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS (SELECT 1 FROM $wpdb->users WHERE ID = %d)", $offset ) );
	}
}
