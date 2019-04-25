<?php
/**
 * ConnectionResolver - Customer_Connection_Resolver
 *
 * Resolves connections to Customers
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Extensions\WooCommerce\Model\Coupon;

/**
 * Class Customer_Connection_Resolver
 */
class Customer_Connection_Resolver extends AbstractConnectionResolver {
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
		 * Set the $query_args based on various defaults and primary input $args
		 */
		$query_args['count_total'] = false;
		$query_args['offset']      = $this->get_offset();
		$query_args['orderby']     = 'ID';
		$query_args['order']       = ! empty( $this->args['last'] ) ? 'ASC' : 'DESC';
		$query_args['number']      = $this->get_query_amount();

		$input_fields = array();
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

		$query_args = apply_filters(
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
	 * @return \WP_Query
	 */
	public function get_query() {
		return new \WP_User_Query( $this->get_query_args() );
	}

	/**
	 * Returns an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		$results = $this->get_query()->get_results();
		return ! empty( $results ) ? $results : array();
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
		$args = array();

		$key_mapping = array(
			'search'    => 'search',
			'exclude'   => 'exclude',
			'include'   => 'include',
			'role'      => 'role',
			'roleIn'    => 'role__in',
			'roleNotIn' => 'role__not_in',
		);

		foreach ( $key_mapping as $key => $field ) {
			if ( ! empty( $where_args[ $key ] ) ) {
				$args[ $field ] = $where_args[ $key ];
			}
		}

		// Filter by email.
		if ( ! empty( $where_args['email'] ) ) {
			$args['search']         = $where_args['email'];
			$args['search_columns'] = array( 'user_email' );
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
}
