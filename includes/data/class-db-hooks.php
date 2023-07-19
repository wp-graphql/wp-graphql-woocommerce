<?php
/**
 * Register hooks for the filtering DB queries.
 *
 * @package WPGraphQL\WooCommerce\Data
 * @since 0.14.0
 */

namespace WPGraphQL\WooCommerce\Data;

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;

/**
 * Class DB_Hooks
 */
class DB_Hooks {
	/**
	 * DB_Hooks constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_orders_table_query_clauses', [ $this, 'add_cursor' ], 10, 3 );
	}

	/**
	 * Add the cursor to the WHERE clause of the query.
	 *
	 * @param string[]                                                            $clauses {
	 *                                                        Associative array of the clauses for the query.
	 *
	 *     @type string $fields  The SELECT clause of the query.
	 *     @type string $join    The JOIN clause of the query.
	 *     @type string $where   The WHERE clause of the query.
	 *     @type string $groupby The GROUP BY clause of the query.
	 *     @type string $orderby The ORDER BY clause of the query.
	 *     @type string $limits  The LIMIT clause of the query.
	 * }
	 * @param \Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableQuery $query The OrdersTableQuery instance (passed by reference).
	 * @param array                                                               $args  Query args.
	 *
	 * @return string[]
	 */
	public function add_cursor( $clauses, $query, $args ) {
		if ( true !== is_graphql_request() ) {
			return $clauses;
		}

		/** @var \Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore $order_datastore */
		$order_datastore = wc_get_container()->get( OrdersTableDataStore::class );
		$tables          = $order_datastore::get_all_table_names_with_id();

		// apply the after cursor to the query.
		if ( ! empty( $args['graphql_after_cursor'] ) ) {
			$after_cursor      = new Cursor\COT_Cursor( $args, $query, 'after' );
			$clauses['where'] .= $after_cursor->get_where();
		}

		// apply the before cursor to the query.
		if ( ! empty( $args['graphql_before_cursor'] ) ) {
			$before_cursor     = new Cursor\COT_Cursor( $args, $query, 'before' );
			$clauses['where'] .= $before_cursor->get_where();
		}

		// If the cursor "graphql_cursor_compare" arg is not in the query,
		// default to using ID DESC as the stabilizer.
		$orderby = ! empty( $clauses['orderby'] ) && is_string( $clauses['orderby'] )
			? $clauses['orderby'] . ','
			: '';

		if ( ! isset( $args['graphql_cursor_compare'] ) ) {
			$clauses['orderby'] = "{$orderby} {$tables['orders']}.id DESC ";
		} else {
			// Check the cursor compare order.
			$order = '>' === $args['graphql_cursor_compare'] ? 'ASC' : 'DESC';

			$clauses['orderby'] = "{$orderby} {$tables['orders']}.id {$order} ";
		}

		return $clauses;
	}
}
