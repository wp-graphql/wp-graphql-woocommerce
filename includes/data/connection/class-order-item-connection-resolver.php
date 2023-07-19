<?php
/**
 * ConnectionResolver - Order_Item_Connection_Resolver
 *
 * Resolves connections to Orders
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * Class Order_Item_Connection_Resolver
 */
class Order_Item_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'order_item';
	}

	/**
	 * Confirms if downloadable items should be retrieved.
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates downloadable item filters.
	 *
	 * @return array
	 */
	public function get_query_args() {
		$query_args = [ 'filters' => [] ];

		/**
		 * Filter the $query_args to allow folks to customize queries programmatically.
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query.
		 * @param mixed       $source     The source that's passed down the GraphQL queries.
		 * @param array       $args       The inputArgs on the field.
		 * @param \WPGraphQL\AppContext  $context    The AppContext passed down the GraphQL tree.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo passed down the GraphQL tree.
		 */
		$query_args = apply_filters( 'graphql_order_item_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return array
	 */
	public function get_query() {
		// @codingStandardsIgnoreLine
		switch ( $this->info->fieldName ) {
			case 'taxLines':
				$type = 'tax';
				break;
			case 'shippingLines':
				$type = 'shipping';
				break;
			case 'feeLines':
				$type = 'fee';
				break;
			case 'couponLines':
				$type = 'coupon';
				break;
			default:
				/**
				 * Filter the $item_type to allow non-core item types.
				 *
				 * @param string      $item_type  Order item type.
				 * @param mixed       $source     The source that's passed down the GraphQL queries.
				 * @param array       $args       The inputArgs on the field.
				 * @param \WPGraphQL\AppContext  $context    The AppContext passed down the GraphQL tree.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo passed down the GraphQL tree.
				 */
				$type = apply_filters(
					'graphql_order_item_connection_item_type',
					'line_item',
					$this->source,
					$this->args,
					$this->context,
					$this->info
				);
				break;
		}//end switch

		$items = [];
		foreach ( $this->source->get_items( $type ) as $item ) {
			$items[] = $item;
		}

		if ( empty( $items ) ) {
			return [];
		}

		if ( ! empty( $this->query_args['filters'] ) && is_array( $this->query_args['filters'] ) ) {
			foreach ( $this->query_args['filters'] as $filter ) {
				$items = array_filter( $items, $filter );
			}
		}

		$cursor = (int) $this->get_offset_for_cursor( $this->args['after'] ?? ( $this->args['before'] ?? 0 ) );
		$first  = ! empty( $this->args['first'] ) ? $this->args['first'] : null;
		$last   = ! empty( $this->args['last'] ) ? $this->args['last'] : null;

		// MUST DO FOR SANITY ~ If last, reverse list for correct slicing.
		if ( $last ) {
			$items = array_reverse( $items );
		}

		$get_item_id = static function ( $item ) {
			return $item->get_id();
		};

		// Set offset.
		$offset = $cursor
			? array_search( $cursor, array_map( $get_item_id, $items ), true )
			: 0;

		if ( false === $offset ) {
			$offset = 0;
		}

		// If cursor set, move index up one to ensure cursor not included in keys.
		if ( $cursor ) {
			$offset++;
		}

		$items = array_slice( $items, $offset, $this->query_amount + 1 );

		// Cache items for later.
		foreach ( $items as $item ) {
			$this->loader->prime(
				$item->get_id(),
				new \WPGraphQL\WooCommerce\Model\Order_Item( $item, $this->source )
			);
		}

		return array_map( $get_item_id, $items );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids() {
		return ! empty( $this->query ) ? $this->query : [];
	}

	/**
	 * Validates offset.
	 *
	 * @param integer $offset Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return 'string' === gettype( $offset );
	}
}
