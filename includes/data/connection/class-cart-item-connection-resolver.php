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

use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * Class Cart_Item_Connection_Resolver
 *
 * @property \WPGraphQL\WooCommerce\Data\Loader\WC_Db_Loader $loader
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
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
		$query_args = [ 'filters' => [] ];
		if ( ! empty( $this->args['where'] ) ) {
			$where_args = $this->args['where'];
			if ( isset( $where_args['needsShipping'] ) ) {
				$needs_shipping          = $where_args['needsShipping'];
				$query_args['filters'][] = static function ( $cart_item ) use ( $needs_shipping ) {
					$product = \WC()->product_factory->get_product( $cart_item['product_id'] );

					if ( ! is_object( $product ) ) {
						return false;
					}

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
		 * @param \WPGraphQL\AppContext  $context    The AppContext passed down the GraphQL tree.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo passed down the GraphQL tree.
		 */
		$query_args = apply_filters( 'graphql_cart_item_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return array
	 */
	public function get_query() {
		$cart_items = array_values( $this->source->get_cart() );

		if ( ! empty( $this->query_args['filters'] ) && is_array( $this->query_args['filters'] ) ) {
			foreach ( $this->query_args['filters'] as $filter ) {
				$cart_items = array_filter( $cart_items, $filter );
			}
		}

		// Cache cart items for later.
		foreach ( $cart_items as $item ) {
			$this->loader->prime( $item['key'], $item );
		}

		// Return cart item keys.
		return array_column( $cart_items, 'key' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ids_from_query() {
		return ! empty( $this->query ) ? $this->query : [];
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
}
