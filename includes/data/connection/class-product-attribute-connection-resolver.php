<?php
/**
 * ConnectionResolver - Product_Attribute_Connection_Resolver
 *
 * Resolves connections to ProductAttributes
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class Product_Attribute_Connection_Resolver
 */
class Product_Attribute_Connection_Resolver {
	/**
	 * Builds Product attribute items
	 *
	 * @param array   $attributes Array of WC_Product_Attributes instances.
	 * @param Product $source     Parent product model.
	 *
	 * @return array
	 */
	private function get_items( $attributes, $source ) {
		$items = array();
		foreach ( $attributes as $attribute_name => $data ) {
			$data->_relay_id = base64_encode( $attribute_name . '||' . $source->ID . '||' . $data->get_id() );
			$items[]         = $data;
		}

		return $items;
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
		$attributes = $this->get_items( $source->attributes, $source );

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
