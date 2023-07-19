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
	 * @param array $attrs       Source Product's WC_Product_Attribute(s) object.
	 * @param int   $product_id  Source Product ID.
	 * @return array
	 */
	public static function product_attributes_to_data_array( $attrs, $product_id ) {
		$attributes = [];

		foreach ( $attrs as $attribute ) {
			if ( ! is_a( $attribute, 'WC_Product_Attribute' ) ) {
				continue;
			}
			$name = $attribute->get_name();

			if ( $attribute->is_taxonomy() ) {
				$attribute_taxonomy = $attribute->get_taxonomy_object();
				$attribute_values   = wc_get_product_terms( $product_id, $attribute->get_name(), [ 'fields' => 'all' ] );
				foreach ( $attribute_values as $attribute_value ) {
					$id           = base64_encode( $product_id . '|' . $name . '|' . $attribute_value->name ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					$attributes[] = [
						'id'          => $id,
						'attributeId' => $attribute_value->term_id,
						'name'        => $name,
						'value'       => $attribute_value->name,
					];
				}
			} else {
				$values = $attribute->get_options();
				foreach ( $values as $attribute_value ) {
					$id           = base64_encode( $product_id . '|' . $name . '|' . $attribute_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					$attributes[] = [
						'id'          => $id,
						'attributeId' => 0,
						'name'        => $name,
						'value'       => $attribute_value,
					];
				}
			}//end if
		}//end foreach

		return $attributes;
	}

	/**
	 * Returns data array from WC_Product_Attribute ArrayAccess object.
	 *
	 * @param array      $attrs         WC_Product_Attribute object.
	 * @param string|int $variation_id  Variable Product or Variation ID.
	 *
	 * @return array
	 */
	public static function variation_attributes_to_data_array( $attrs, $variation_id ) {
		$attributes = [];

		// Bail early if explicitly '0' attributes.
		if ( [ '0' ] === $attrs ) {
			return $attributes;
		}

		foreach ( $attrs as $name => $value ) {
			$term = \get_term_by( 'slug', $value, $name );

			// ID create for caching only, not object retrieval.
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$id = base64_encode( $variation_id . '||' . $name . '||' . $value );

			if ( ! $term instanceof \WP_Term ) {
				$attributes[] = [
					'id'          => $id,
					'attributeId' => 0,
					'name'        => $name,
					'value'       => $value,
				];
			} else {
				$attributes[] = [
					'id'          => $id,
					'attributeId' => $term->term_id,
					'name'        => $term->taxonomy,
					'value'       => $term->name,
				];
			}
		}//end foreach

		return $attributes;
	}

	/**
	 * Creates connection
	 *
	 * @param mixed                                $source     - Connection source Model instance.
	 * @param array                                $args       - Connection arguments.
	 * @param \WPGraphQL\AppContext                $context    - AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array|null
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		if ( is_a( $source, Product::class ) && 'simple' === $source->get_type() ) {
			$attributes = self::product_attributes_to_data_array( $source->attributes, $source->ID );
		} else {
			$attributes = self::variation_attributes_to_data_array(
				is_a( $source, Product::class ) ? $source->default_attributes : $source->attributes,
				$source->ID
			);
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
