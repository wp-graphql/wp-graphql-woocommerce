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
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 */
class Variation_Attribute_Connection_Resolver {

	/**
	 * Returns data array from WC_Product_Attribute ArrayAccess object.
	 *
	 * @param array      $attrs      WC_Product_Attribute object.
	 * @param string|int $parent_id  ProductVariation Relay ID.
	 *
	 * @return array
	 */
	public static function to_data_array( $attrs = [], $parent_id = 0 ) {
		$attributes = [];

		// Bail early if explicitly '0' attributes.
		if ( [ '0' ] === $attrs ) {
			return $attributes;
		}

		foreach ( $attrs as $name => $value ) {
			$term = \get_term_by( 'slug', $value, $name );

			// ID create for caching only, not object retrieval.
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$id = base64_encode( $parent_id . '||' . $name . '||' . $value );

			if ( ! $term instanceof \WP_Term ) {
				$attributes[] = [

					'id'          => $id,
					'attributeId' => 0,
					'name'        => $name,
					'value'       => $value,
				];

				continue;
			}

			$attributes[] = [
				'id'          => $id,
				'attributeId' => $term->term_id,
				'name'        => $term->taxonomy,
				'value'       => $term->name,
			];
		}//end foreach

		return $attributes;
	}

	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array|null
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		if ( is_a( $source, Product::class ) ) {
			$attributes = self::to_data_array( $source->default_attributes, $source->ID );
		} else {
			$attributes = self::to_data_array( $source->attributes, $source->ID );
		}

		$connection = Relay::connectionFromArray( $attributes, $args );
		$nodes      = [];
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $attributes ) ? $connection : null;
	}
}
