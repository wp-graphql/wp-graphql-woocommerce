<?php
/**
 * Defines the fields for products with variations.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Connection\Products;
use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;
use WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver;

/**
 * Class Products_With_Variations
 */
class Products_With_Variations {
	/**
	 * Registers the "ProductsWithVariations" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductsWithVariations',
			[
				'description' => __( 'Products with variations.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'connections' => self::get_connections(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines "ProductsWithVariations" fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'         => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			],
			'databaseId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines "ProductsWithVariations" connections.
	 *
	 * @return array
	 */
	public static function get_connections() {
		return [
			'variations' => [
				'toType'         => 'ProductVariation',
				'connectionArgs' => Products::get_connection_args(),
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

					$resolver->set_query_arg( 'post_parent', $source->ID );
					$resolver->set_query_arg( 'post_type', 'product_variation' );
					$resolver->set_query_arg( 'post__in', $source->variation_ids );

					return $resolver->get_connection();
				},
			],
		];
	}
}
