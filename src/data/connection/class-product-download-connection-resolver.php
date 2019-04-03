<?php
/**
 * ConnectionResolver - Product_Download_Connection_Resolver
 *
 * Resolves connections to ProductDownloads
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Extension\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Product;
/**
 * Class Product_Download_Connection_Resolver
 */
class Product_Download_Connection_Resolver {
	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		switch ( true ) {
			case is_a( $source, Product::class ):
				// @codingStandardsIgnoreLine
				if ( 'downloads' === $info->fieldName ) {
					$downloads = $source->downloads;
				}
				break;
			case is_a( $source, Order::class ):
				// @codingStandardsIgnoreLine
				if ( 'downloadableItems' === $info->fieldName ) {
					$downloads = $source->downloadable_items;
				}
				break;
			default:
				break;
		}

		$connection = Relay::connectionFromArray( $downloads, $args );
		$nodes      = array();
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $downloads ) ? $connection : null;
	}
}
