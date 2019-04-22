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
		$query_args['order']       = ! empty( $this->args['last'] ) ? 'ASC' : 'DESC';

		/**
		 * If "pageInfo" is in the fieldSelection, we need to calculate the pagination details, so
		 * we need to run the query with count_total set to true.
		 */
		$field_selection = $this->info->getFieldSelection( 2 );
		if ( ! empty( $field_selection['pageInfo'] ) ) {
			$query_args['count_total'] = true;
		}

		$query_args['number'] = $this->get_query_amount();

		if ( true === is_object( $this->source ) ) {
			switch ( true ) {
				case is_a( $this->source, Coupon::class ):
					if ( 'usedBy' === $this->info->fieldName ) {
						$query_args['include'] = $this->source->used_by_ids;
					}
					break;
				default:
					break;
			}
		}

		$query_args['fields'] = 'ids';

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
		return ! empty( $results ) ? $results : [];
	}
}
