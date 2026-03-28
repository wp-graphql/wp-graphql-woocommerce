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
		add_filter( 'woocommerce_order_query_args', [ $this, 'clean_query_vars' ] );
		add_filter( 'woocommerce_orders_table_query_clauses', [ $this, 'add_cursor' ], 10, 3 );
	}

	/**
	 * Meta key to COT column mapping for orderby translation.
	 *
	 * @var array
	 */
	private static $meta_to_column = [
		'_order_total'    => 'total',
		'_order_tax'      => 'tax_amount',
		'_cart_discount'  => 'discount_total_amount',
		'_date_paid'      => 'date_paid',
		'_date_completed' => 'date_completed',
		'_order_key'      => 'payment_method',
	];

	/**
	 * Translate legacy meta_key orderby to COT column orderby in query vars.
	 *
	 * When HPOS is active, meta keys like _order_total are stored as columns
	 * in the orders table, not in the meta table. This replaces meta_value_num
	 * orderby with the direct column name so WC's OrdersTableQuery doesn't
	 * generate an invalid meta join.
	 *
	 * @param array $query_vars The query vars.
	 *
	 * @return array
	 */
	public function clean_query_vars( $query_vars ) {
		if ( true !== is_graphql_request() ) {
			return $query_vars;
		}

		if ( ! \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return $query_vars;
		}

		// Map post_* orderby fields to COT-compatible equivalents.
		$post_field_map = [
			'post_date'     => 'date',
			'post_modified' => 'date_modified',
			'post_parent'   => 'parent',
		];

		$orderby = $query_vars['orderby'] ?? [];
		if ( is_array( $orderby ) ) {
			foreach ( $post_field_map as $post_field => $cot_field ) {
				if ( isset( $orderby[ $post_field ] ) ) {
					$orderby[ $cot_field ] = $orderby[ $post_field ];
					unset( $orderby[ $post_field ] );
				}
			}
			$query_vars['orderby'] = $orderby;
		}

		// Map meta_key orderby to COT column orderby.
		$meta_key = $query_vars['meta_key'] ?? '';
		if ( empty( $meta_key ) || ! isset( self::$meta_to_column[ $meta_key ] ) ) {
			return $query_vars;
		}

		$column        = self::$meta_to_column[ $meta_key ];
		$default_order = isset( $query_vars['graphql_cursor_compare'] ) && '>' === $query_vars['graphql_cursor_compare']
			? 'DESC'
			: 'ASC';

		$orderby = $query_vars['orderby'] ?? [];
		if ( is_array( $orderby ) ) {
			$order = $orderby['meta_value_num'] ?? ( $orderby['meta_value'] ?? $default_order );
			unset( $orderby['meta_value_num'], $orderby['meta_value'] );
			$orderby[ $column ] = $order;
			$query_vars['orderby'] = $orderby;
		}

		unset( $query_vars['meta_key'] );

		return $query_vars;
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
