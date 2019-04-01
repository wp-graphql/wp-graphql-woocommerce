<?php
/**
 * ConnectionResolver - Order_Connection_Resolver
 *
 * Resolves connections to Orders
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Extension\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;

/**
 * Class Order_Connection_Resolver
 */
class Order_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Confirms the uses has the privileges to query Orders
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
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
			'post_type'      => 'shop_order',
			'post_status'    => 'any',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

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
}
