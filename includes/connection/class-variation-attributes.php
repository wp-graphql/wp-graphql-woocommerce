<?php
/**
 * Connection type - VariationAttributes
 *
 * Registers connections to VariationAttribute
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.4
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Variation_Attribute_Connection_Resolver;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class Product_Attributes
 */
class Variation_Attributes {
	/**
	 * Registers the various connections from other Types to VariationAttribute
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From ProductVariation.
		register_graphql_connection( self::get_connection_config() );

		// From product types.
		$product_types = array_values( WP_GraphQL_WooCommerce::get_enabled_product_types() );
		foreach ( $product_types as $product_type ) {
			register_graphql_connection(
				self::get_connection_config(
					[
						'fromType'      => $product_type,
						'fromFieldName' => 'defaultAttributes',
					]
				)
			);
		}
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = [] ): array {
		return array_merge(
			[
				'fromType'       => 'ProductVariation',
				'toType'         => 'VariationAttribute',
				'fromFieldName'  => 'variationAttributes',
				'connectionArgs' => [],
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Variation_Attribute_Connection_Resolver();

					return $resolver->resolve( $source, $args, $context, $info );
				},
			],
			$args
		);
	}
}
