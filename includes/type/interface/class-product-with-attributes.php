<?php
/**
 * Defines the "ProductWithAttributes" interface.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;
use WPGraphQL\WooCommerce\Data\Connection\Variation_Attribute_Connection_Resolver;

/**
 * Class Product_With_Attributes
 */
class Product_With_Attributes {
	/**
	 * Registers the "ProductWithAttributes" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductWithAttributes',
			[
				'description' => __( 'Products with default attributes.', 'wp-graphql-woocommerce' ),
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
			'defaultAttributes' => [
				'toType'        => 'VariationAttribute',
				'fromFieldName' => 'defaultAttributes',
				'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Variation_Attribute_Connection_Resolver();

					return $resolver->resolve( $source, $args, $context, $info );
				},
			],
		];
	}
}
