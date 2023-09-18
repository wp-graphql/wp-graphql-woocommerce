<?php
/**
 * Defines the union between product types and product variation types.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Product_Union
 */
class Product_Union {
	/**
	 * Registers the Type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductUnion',
			[
				'description' => __( 'Union between the product and product variation types', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines ProductUnion fields. All child type must have these fields as well.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array_merge(
			[
				'id'                => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
				],
				'databaseId'        => [
					'type'        => [ 'non_null' => 'Int' ],
					'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
				],
				'slug'              => [
					'type'        => 'String',
					'description' => __( 'Product slug', 'wp-graphql-woocommerce' ),
				],
				'type'              => [
					'type'        => 'ProductTypesEnum',
					'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
				],
				'name'              => [
					'type'        => 'String',
					'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
				],
				'featured'          => [
					'type'        => 'Boolean',
					'description' => __( 'If the product is featured', 'wp-graphql-woocommerce' ),
				],
				'catalogVisibility' => [
					'type'        => 'CatalogVisibilityEnum',
					'description' => __( 'Catalog visibility', 'wp-graphql-woocommerce' ),
				],
				'sku'               => [
					'type'        => 'String',
					'description' => __( 'Product SKU', 'wp-graphql-woocommerce' ),
				],
				'description'       => [
					'type'        => 'String',
					'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PostObjectFieldFormatEnum',
							'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->descriptionRaw;
						}
						return $source->description;
					},
				],
				'image'             => [
					'type'        => 'MediaItem',
					'description' => __( 'Main image', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						// @codingStandardsIgnoreLine.
						if ( empty( $source->image_id ) || ! absint( $source->image_id ) ) {
							return null;
						}
						return $context->get_loader( 'post' )->load_deferred( $source->image_id );
					},
				],
				'onSale'            => [
					'type'        => 'Boolean',
					'description' => __( 'Is product on sale?', 'wp-graphql-woocommerce' ),
				],
				'purchasable'       => [
					'type'        => 'Boolean',
					'description' => __( 'Can product be purchased?', 'wp-graphql-woocommerce' ),
				],
			],
			Product::get_fields()
		);
	}
}
