<?php

namespace WPGraphQL\Extensions\WooCommerce\Data;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class ProductAttributeConnectionResolver - Connects the product attributes to other products
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since 0.0.1
 */
class ProductAttributeConnectionResolver {
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
    if ( 'defaultAttributes' === $info->fieldName ) {
      $attributes = $source->get_default_attributes();
    } else {
      $attributes = $source->get_attributes();
    }
		
		$connection = Relay::connectionFromArray( $attributes, $args );
		$nodes = [];
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $attributes ) ? $connection : null;
	}
}