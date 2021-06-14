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
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Cart_Item_Connection_Resolver
 */
class Cart_Item_Connection_Resolver extends AbstractConnectionResolver {

	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'cart_item';
	}

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

		$cursor = $this->get_offset();
		$first  = ! empty( $this->args['first'] ) ? $this->args['first'] : null;
		$last   = ! empty( $this->args['last'] ) ? $this->args['last'] : null;

		// MUST DO FOR SANITY ~ If last, reverse list for correct slicing.
		if ( $last ) {
			$cart_items = array_reverse( $cart_items );
		}

		// Set offset.
		$offset = $cursor
			? array_search( $cursor, array_column( $cart_items, 'key' ), true )
			: 0;

		// If cursor set, move index up one to ensure cursor not included in keys.
		if ( $cursor ) {
			$offset++;
		}

		$cart_items = array_slice( $cart_items, $offset, $this->query_amount + 1 );

		// Cache cart items for later.
		foreach ( $cart_items as $item ) {
			$this->loader->prime( $item['key'], $item );
		}

		// Return cart item keys.
		return array_column( $cart_items, 'key' );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids() {
		return ! empty( $this->query ) ? $this->query : array();
	}

	/**
	 * Check if cart item key is valid by confirming the validity of
	 * the cart item in the cart encoded into cart item key.
	 *
	 * @param string $offset  Cart item key.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return ! empty( $this->source->get_cart_item( $offset ) );
	}

	/**
	 * Validates cart item model.
	 *
	 * @param array $model Cart item model.
	 *
	 * @return bool
	 */
	protected function is_valid_model( $model ) {
		return ! empty( $model ) && ! empty( $model['key'] ) && ! empty( $model['product_id'] );
	}

	/**
	 * Get_offset
	 *
	 * This returns the offset to be used in the $query_args based on the $args passed to the
	 * GraphQL query.
	 *
	 * @return int|mixed
	 */
	public function get_offset() {
		/**
		 * Defaults
		 */
		$offset = 0;

		/**
		 * Get the $after offset
		 */
		if ( ! empty( $this->args['after'] ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$offset = substr( base64_decode( $this->args['after'] ), strlen( 'arrayconnection:' ) );
		} elseif ( ! empty( $this->args['before'] ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$offset = substr( base64_decode( $this->args['before'] ), strlen( 'arrayconnection:' ) );
		}

		/**
		 * Return the higher of the two values
		 */
		return $offset;
	}
}
