<?php

namespace WPGraphQL\Extensions\WooCommerce\Data;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Product_Download_Connection_Resolver - Connects the product downloads to products
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since 0.0.1
 */
class Product_Download_Connection_Resolver {

	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
        $downloads = $source->get_downloads();

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