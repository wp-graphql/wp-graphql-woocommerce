<?php
/**
 * ConnectionResolver - Variation_Attribute_Connection_Resolver
 *
 * Resolves connections to VariationAttributes
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.4
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class Variation_Attribute_Connection_Resolver
 */
class Variation_Attribute_Connection_Resolver {

	/**
	 * Returns data array from WC_Product_Attribute ArrayAccess object.
	 *
	 * @param array  $attrs      WC_Product_Attribute object.
	 * @param string $parent_id  ProductVariation Relay ID.
	 *
	 * @return array
	 */
	public static function to_data_array( $attrs = array(), $parent_id = 0 ) {
		$attributes = array();
		if ( array( '0' ) !== $attrs ) {
			foreach ( $attrs as $name => $value ) {
				$term = \get_term_by( 'slug', $value, $name );
				if ( empty( $term ) ) {
					$attributes[] = array(
						// ID create for caching only, not object retrieval.
						'id'          => base64_encode( $parent_id . '||' . $name . '||' . $value ),
						'attributeId' => 0,
						'name'        => $name,
						'value'       => $value,
					);
				} else {
					$attributes[] = array(
						// ID create for caching only, not object retrieval.
						'id'          => base64_encode( $parent_id . '||' . $name . '||' . $value ),
						'attributeId' => $term->term_id,
						'name'        => $term->taxonomy,
						'value'       => $term->name,
					);
				}
			}
		}

		return $attributes;
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
		if ( is_a( $source, Product::class ) ) {
			$attributes = self::to_data_array( $source->default_attributes, $source->ID );
		} else {
			$attributes = self::to_data_array( $source->attributes, $source->ID );
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
