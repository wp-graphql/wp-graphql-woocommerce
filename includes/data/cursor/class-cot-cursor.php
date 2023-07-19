<?php
/**
 * COT Cursor
 *
 * This class generates the SQL AND operators for cursor based pagination
 * for the custom orders table/HPOS
 *
 * @package WPGraphQL\WooCommerce\Data\Cursor;
 * @since   0.14.0
 */

namespace WPGraphQL\WooCommerce\Data\Cursor;

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;
use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableMetaQuery;
use WPGraphQL\Data\Cursor\AbstractCursor;

/**
 * Class COT_Cursor
 */
class COT_Cursor extends AbstractCursor {
	/**
	 * Stores the cursory order node
	 *
	 * @var ?\WC_Abstract_Order
	 */
	public $cursor_node;

	/**
	 * Counter for meta value joins
	 *
	 * @var integer
	 */
	public $meta_join_alias = 0;

	/**
	 * The query instance to use when building the SQL statement.
	 *
	 * @var \Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableQuery
	 */
	public $query;

	/**
	 * Names of all COT tables (orders, addresses, operational_data, meta) in the form 'table_id' => 'table name'.
	 *
	 * @var array
	 */
	private $tables;

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	private $column_mappings;

	/**
	 * Meta query parser.
	 *
	 * @var \Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableMetaQuery|null
	 */
	private $meta_query;

	/**
	 * COT_Cursor constructor.
	 *
	 * @param array                                                               $query_vars  The query vars to use when building the SQL statement.
	 * @param \Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableQuery $query       The query to use when building the SQL statement.
	 * @param string|null                                                         $cursor      Whether to generate the before or after cursor. Default "after".
	 */
	public function __construct( $query_vars, $query, $cursor = 'after' ) {
		// Initialize the class properties.
		parent::__construct( $query_vars, $cursor );

		$this->query = $query;

		// Get tables.
		/** @var \Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore $order_datastore */
		$order_datastore       = wc_get_container()->get( OrdersTableDataStore::class );
		$this->tables          = $order_datastore::get_all_table_names_with_id();
		$mappings              = $order_datastore->get_all_order_column_mappings();
		$this->column_mappings = [];
		foreach ( $mappings['orders'] as $column => $meta_value ) {
			$this->column_mappings[ "{$this->tables['orders']}.{$column}" ] = $meta_value['name'];
		}

		if ( ! is_null( $this->get_query_var( 'meta_query' ) ) ) {
			$this->meta_query = new OrdersTableMetaQuery( $this->query );
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ?\WC_Abstract_Order
	 */
	public function get_cursor_node() {
		// If we have a bad cursor, just skip.
		if ( ! $this->cursor_offset ) {
			return null;
		}

		// Get the order.
		$order = wc_get_order( $this->cursor_offset );

		return $order instanceof \WC_Abstract_Order ? $order : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function to_sql() {
		$orderby = isset( $this->query_vars['orderby'] ) ? $this->query_vars['orderby'] : null;

		$orderby_should_not_convert_to_sql = isset( $orderby ) && in_array(
			$orderby,
			[
				'include',
				'id',
				'parent_order_id',
			],
			true
		);

		if ( true === $orderby_should_not_convert_to_sql ) {
			return '';
		}

		$sql = $this->builder->to_sql();

		if ( empty( $sql ) ) {
			return '';
		}

		return ' AND ' . $sql;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_where() {
		// If we have a bad cursor, just skip.
		if ( ! $this->is_valid_offset_and_node() ) {
			return '';
		}

		$orderby = $this->get_query_var( 'orderby' );
		$order   = $this->get_query_var( 'order' );

		if ( ! empty( $orderby ) && is_array( $orderby ) ) {

			/**
			 * Loop through all order keys if it is an array
			 */
			foreach ( $orderby as $by => $order ) {
				$this->compare_with( $by, $order );
			}
		} elseif ( ! empty( $orderby ) && is_string( $orderby ) ) {

			/**
			 * If $orderby is just a string just compare with it directly as DESC
			 */
			$this->compare_with( $orderby, $order );
		}

		/**
		 * No custom comparing. Use the default date
		 */
		if ( ! $this->builder->has_fields() ) {
			$this->compare_with_date();
		}

		$this->builder->add_field( "{$this->tables['orders']}.id", $this->cursor_offset, 'ID', $order );

		return $this->to_sql();
	}

	/**
	 * Get AND operator for given order by key
	 *
	 * @param string $by    The order by key.
	 * @param string $order The order direction ASC or DESC.
	 *
	 * @return void
	 */
	private function compare_with( $by, $order ) {
		if ( null === $this->cursor_node ) {
			return;
		}

		$meta_orderby_keys = $this->meta_query ? $this->meta_query->get_orderby_keys() : [];

		if ( in_array( $by, $meta_orderby_keys, true ) && null !== $this->meta_query ) {
			$orderby = $this->meta_query->get_orderby_clause_for_key( $by );
			$value   = $this->cursor_node->get_meta( $by, true ) ?? null;
		} else {
			$orderby     = $by;
			$getter_name = $this->column_mappings[ $orderby ];

			$method = "get_{$getter_name}";
			$value  = is_callable( [ $this->cursor_node, $method ] ) ? $this->cursor_node->$method() : null;
		}

		if ( ! empty( $value ) && is_a( $value, '\WC_DateTime' ) ) {
			$value = ( new \DateTime( $value ) )->setTimezone( new \DateTimeZone( '+00:00' ) )->format( 'Y-m-d H:i:s' );
			$this->builder->add_field( $by, $value, 'DATETIME', $order );
			return;
		}

		/**
		 * Compare by the post field if the key matches a value
		 */
		if ( ! empty( $value ) ) {
			$this->builder->add_field( $by, $value, null, $order );
		}
	}

	/**
	 * Use post date based comparison
	 *
	 * @return void
	 */
	private function compare_with_date() {
		$value = null;
		if ( null !== $this->cursor_node ) {
			$date_created = $this->cursor_node->get_date_created();
			$value        = ! empty( $date_created ) ? ( new \DateTime( $date_created ) )->setTimezone( new \DateTimeZone( '+00:00' ) )->format( 'Y-m-d H:i:s' ) : null;
		}

		$this->builder->add_field( "{$this->tables['orders']}.date_created_gmt", $value, 'DATETIME' );
	}
}
