<?php
/**
 * ConnectionResolver - Product_Attribute_Connection_Resolver
 *
 * Resolves connections to ProductAttributes
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Product_Attribute_Connection_Resolver
 */
class Product_Attribute_Connection_Resolver {
	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		// @codingStandardsIgnoreStart
		if ( 'defaultAttributes' === $info->fieldName ) {
		// @codingStandardsIgnoreEnd
			$attributes = $source->default_attributes;
		} else {
			$attributes = $source->attributes;
		}

		$connection = Relay::connectionFromArray( $attributes, $args );
		$nodes      = array();
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $attributes ) ? $connection : null;
	}
}
