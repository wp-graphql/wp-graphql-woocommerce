<?php
/**
 * WPInputObjectType - CollectionStatsWhereArgs
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.18.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Collection_Stats_Where_Args
 */
class Collection_Stats_Where_Args {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'CollectionStatsWhereArgs',
			[
				'description' => static function () {
					return __( 'Arguments used to filter the collection results', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'search'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Limit result set to products based on a keyword search.', 'graphql-for-ecommerce' );
						},
					],
					'slugIn'       => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Limit result set to products with specific slugs.', 'graphql-for-ecommerce' );
						},
					],
					'typeIn'       => [
						'type'        => [ 'list_of' => 'ProductTypesEnum' ],
						'description' => static function () {
							return __( 'Limit result set to products assigned to a group of specific types.', 'graphql-for-ecommerce' );
						},
					],
					'exclude'      => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'Ensure result set excludes specific IDs.', 'graphql-for-ecommerce' );
						},
					],
					'include'      => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'Limit result set to specific ids.', 'graphql-for-ecommerce' );
						},
					],
					'sku'          => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'graphql-for-ecommerce' );
						},
					],
					'featured'     => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Limit result set to featured products.', 'graphql-for-ecommerce' );
						},
					],
					'parentIn'     => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'Specify objects whose parent is in an array.', 'graphql-for-ecommerce' );
						},
					],
					'parentNotIn'  => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'Specify objects whose parent is not in an array.', 'graphql-for-ecommerce' );
						},
					],
					'categoryIn'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Limit result set to products assigned to a group of specific categories by name.', 'graphql-for-ecommerce' );
						},
					],
					'categoryIdIn' => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'Limit result set to products assigned to a specific group of category IDs.', 'graphql-for-ecommerce' );
						},
					],
					'tagIn'        => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Limit result set to products assigned to a specific group of tags by name.', 'graphql-for-ecommerce' );
						},
					],
					'tagIdIn'      => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'Limit result set to products assigned to a specific group of tag IDs.', 'graphql-for-ecommerce' );
						},
					],
					'attributes'   => [
						'type'        => 'ProductAttributeQueryInput',
						'description' => static function () {
							return __( 'Limit result set to products with selected global attribute queries.', 'graphql-for-ecommerce' );
						},
					],
					'stockStatus'  => [
						'type'        => [ 'list_of' => 'StockStatusEnum' ],
						'description' => static function () {
							return __( 'Limit result set to products in stock or out of stock.', 'graphql-for-ecommerce' );
						},
					],
					'onSale'       => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Limit result set to products on sale.', 'graphql-for-ecommerce' );
						},
					],
					'minPrice'     => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Limit result set to products based on a minimum price.', 'graphql-for-ecommerce' );
						},
					],
					'maxPrice'     => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Limit result set to products based on a maximum price.', 'graphql-for-ecommerce' );
						},
					],
					'visibility'   => [
						'type'        => 'CatalogVisibilityEnum',
						'description' => static function () {
							return __( 'Limit result set to products with a specific visibility level.', 'graphql-for-ecommerce' );
						},
					],
					'rating'       => [
						'type'        => [ 'list_of' => 'Integer' ],
						'description' => static function () {
							return __( 'Limit result set to products with a specific average rating. Must be between 1 and 5', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
