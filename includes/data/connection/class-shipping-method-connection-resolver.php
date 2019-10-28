<?php
/**
 * ConnectionResolver - Shipping_Method_Connection_Resolver
 *
 * Resolves connections to ShippingMethod
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Shipping_Method_Connection_Resolver
 */
class Shipping_Method_Connection_Resolver {
	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$wc_shipping = \WC_Shipping::instance();
		$methods     = $wc_shipping->get_shipping_methods();

		// Get shipping method IDs.
		$methods = array_map(
			function( $item ) {
				return $item->id;
			},
			array_values( $methods )
		);

		$connection = Relay::connectionFromArray( $methods, $args );
		$nodes      = array();
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $methods ) ? $connection : null;
	}
}
