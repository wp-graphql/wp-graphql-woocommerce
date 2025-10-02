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
			array(
				'description' => __( 'Arguments used to filter the collection results', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'search'       => array(
						'type'        => 'String',
						'description' => __( 'Limit result set to products based on a keyword search.', 'wp-graphql-woocommerce' ),
					),
					'slugIn'       => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Limit result set to products with specific slugs.', 'wp-graphql-woocommerce' ),
					),
					'typeIn'       => array(
						'type'        => array( 'list_of' => 'ProductTypesEnum' ),
						'description' => __( 'Limit result set to products assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
					),
					'exclude'      => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'Ensure result set excludes specific IDs.', 'wp-graphql-woocommerce' ),
					),
					'include'      => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'Limit result set to specific ids.', 'wp-graphql-woocommerce' ),
					),
					'sku'          => array(
						'type'        => 'String',
						'description' => __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'wp-graphql-woocommerce' ),
					),
					'featured'     => array(
						'type'        => 'Boolean',
						'description' => __( 'Limit result set to featured products.', 'wp-graphql-woocommerce' ),
					),
					'parentIn'     => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'Specify objects whose parent is in an array.', 'wp-graphql-woocommerce' ),
					),
					'parentNotIn'  => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'Specify objects whose parent is not in an array.', 'wp-graphql-woocommerce' ),
					),
					'categoryIn'   => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Limit result set to products assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
					),
					'categoryIdIn' => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'Limit result set to products assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
					),
					'tagIn'        => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Limit result set to products assigned to a specific group of tags by name.', 'wp-graphql-woocommerce' ),
					),
					'tagIdIn'      => array(
						'type'        => array( 'list_of' => 'Int' ),
						'description' => __( 'Limit result set to products assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
					),
					'attributes'   => array(
						'type'        => 'ProductAttributeQueryInput',
						'description' => __( 'Limit result set to products with selected global attribute queries.', 'wp-graphql-woocommerce' ),
					),
					'stockStatus'  => array(
						'type'        => array( 'list_of' => 'StockStatusEnum' ),
						'description' => __( 'Limit result set to products in stock or out of stock.', 'wp-graphql-woocommerce' ),
					),
					'onSale'       => array(
						'type'        => 'Boolean',
						'description' => __( 'Limit result set to products on sale.', 'wp-graphql-woocommerce' ),
					),
					'minPrice'     => array(
						'type'        => 'Float',
						'description' => __( 'Limit result set to products based on a minimum price.', 'wp-graphql-woocommerce' ),
					),
					'maxPrice'     => array(
						'type'        => 'Float',
						'description' => __( 'Limit result set to products based on a maximum price.', 'wp-graphql-woocommerce' ),
					),
					'visibility'   => array(
						'type'        => 'CatalogVisibilityEnum',
						'description' => __( 'Limit result set to products with a specific visibility level.', 'wp-graphql-woocommerce' ),
					),
					'rating'       => array(
						'type'        => array( 'list_of' => 'Integer' ),
						'description' => __( 'Limit result set to products with a specific average rating. Must be between 1 and 5', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
