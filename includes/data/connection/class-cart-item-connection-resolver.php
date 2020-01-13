<?php
/**
 * ConnectionResolver - Cart_Item_Connection_Resolver
 *
 * Resolves connections to CartItem
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.3
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * Class Cart_Item_Connection_Resolver
 */
class Cart_Item_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Include shared connection functions.
	 */
	use WC_Connection_Functions;

	/**
	 * Confirms if cart items should be retrieved.
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates cart item filters.
	 *
	 * @return array
	 */
	public function get_query_args() {
		$query_args = array( 'filters' => array() );
		if ( ! empty( $this->args['where'] ) ) {
			$where_args = $this->args['where'];
			if ( isset( $where_args['needsShipping'] ) ) {
				$needs_shipping          = $where_args['needsShipping'];
				$query_args['filters'][] = function( $cart_item ) use ( $needs_shipping ) {
					$product = \WC()->product_factory->get_product( $cart_item['product_id'] );
					return $needs_shipping === (bool) $product->needs_shipping();
				};
			}
		}

		/**
		 * Filter the $query_args to allow folks to customize queries programmatically.
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query.
		 * @param mixed       $source     The source that's passed down the GraphQL queries.
		 * @param array       $args       The inputArgs on the field.
		 * @param AppContext  $context    The AppContext passed down the GraphQL tree.
		 * @param ResolveInfo $info       The ResolveInfo passed down the GraphQL tree.
		 */
		$query_args = apply_filters( 'graphql_cart_item_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return \WP_Query
	 */
	public function get_query() {
		$cart_items = array_values( $this->source->get_cart() );

		if ( ! empty( $this->query_args['filters'] ) && is_array( $this->query_args['filters'] ) ) {
			foreach ( $this->query_args['filters'] as $filter ) {
				$cart_items = array_filter( $cart_items, $filter );
			}
		}

		$cursor_key    = $this->get_offset();
		$cursor_offset = array_search( $cursor_key, \array_column( $cart_items, 'key' ), true );

		if ( ! empty( $this->args['after'] ) ) {
			$cart_items = array_splice( $cart_items, $cursor_offset + 1 );
		} elseif ( $cursor_offset ) {
			$cart_items = array_splice( $cart_items, 0, $cursor_offset );
		}

		return array_values( $cart_items );
	}

	/**
	 * This returns the offset to be used in the $query_args based on the $args passed to the
	 * GraphQL query.
	 *
	 * @return int|mixed
	 */
	public function get_offset() {
		$offset = null;

		// Get the offset.
		if ( ! empty( $this->args['after'] ) ) {
			$offset = $this->args['after'];
		} elseif ( ! empty( $this->args['before'] ) ) {
			$offset = $this->args['before'];
		}

		/**
		 * Return the higher of the two values
		 */
		return $offset;
	}

	/**
	 * Create cursor for cart item node.
	 *
	 * @param array  $node  Cart item.
	 * @param string $key   Cart item key.
	 *
	 * @return string
	 */
	protected function get_cursor_for_node( $node, $key = null ) {
		return $node['key'];
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		return ! empty( $this->query ) ? $this->query : array();
	}

	/**
	 * Wrapper for "WC_Connection_Functions::is_valid_cart_item_offset()"
	 *
	 * @param integer $offset Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return $this->is_valid_cart_item_offset( $offset );
	}
}
