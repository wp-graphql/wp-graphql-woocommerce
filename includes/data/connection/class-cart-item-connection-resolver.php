<?php
/**
 * ConnectionResolver - Cart_Item_Connection_Resolver
 *
 * Resolves connections to CartItem
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.3
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Cart_Item_Connection_Resolver
 */
class Cart_Item_Connection_Resolver {
	/**
	 * Returns an array of cart items filtered based upon query arguments
	 *
	 * @param array $items - Cart items.
	 * @param array $args  - Query arguments.
	 *
	 * @return array
	 */
	public function filter( $items, $args = array() ) {
		$filter_items = array_values( $items );

		usort(
			$filter_items,
			function( $item_a, $item_b ) {
				return strcmp( $item_a['key'], $item_b['key'] );
			}
		);

		return $filter_items;
	}

	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$items = $this->filter( $source->get_cart(), $args );

		$connection = Relay::connectionFromArray( $items, $args );
		$nodes      = array();
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $items ) ? $connection : null;
	}
}
