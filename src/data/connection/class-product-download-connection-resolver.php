<?php
/**
 * Connection resolver - ProductDownloads
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

/**
 * Class Product_Download_Connection_Resolver
 */
class Product_Download_Connection_Resolver {
	/**
	 * Creates connection
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
        $downloads = $source->downloads;

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