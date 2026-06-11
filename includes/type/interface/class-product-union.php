<?php
/**
 * Defines the union between product types and product variation types.
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\AppContext;

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
				'description' => static function () {
					return __( 'Union between the product and product variation types', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => 'wc_graphql_resolve_product_type',
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
					'description' => static function () {
						return __( 'Product or variation global ID', 'graphql-for-ecommerce' );
					},
				],
				'databaseId'        => [
					'type'        => [ 'non_null' => 'Int' ],
					'description' => static function () {
						return __( 'Product or variation ID', 'graphql-for-ecommerce' );
					},
				],
				'slug'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Product slug', 'graphql-for-ecommerce' );
					},
				],
				'type'              => [
					'type'        => 'ProductTypesEnum',
					'description' => static function () {
						return __( 'Product type', 'graphql-for-ecommerce' );
					},
				],
				'name'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Product name', 'graphql-for-ecommerce' );
					},
				],
				'featured'          => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'If the product is featured', 'graphql-for-ecommerce' );
					},
				],
				'catalogVisibility' => [
					'type'        => 'CatalogVisibilityEnum',
					'description' => static function () {
						return __( 'Catalog visibility', 'graphql-for-ecommerce' );
					},
				],
				'sku'               => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Product SKU', 'graphql-for-ecommerce' );
					},
				],
				'description'       => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Product description', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PostObjectFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the field output', 'graphql-for-ecommerce' );
							},
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
					'description' => static function () {
						return __( 'Main image', 'graphql-for-ecommerce' );
					},
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
					'description' => static function () {
						return __( 'Is product on sale?', 'graphql-for-ecommerce' );
					},
				],
				'purchasable'       => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Can product be purchased?', 'graphql-for-ecommerce' );
					},
				],
			],
			Product::get_fields()
		);
	}
}
