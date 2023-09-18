<?php
/**
 * Defines the "InventoriedProduct" interface.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Inventoried_Product
 */
class Inventoried_Product {
	/**
	 * Registers the "InventoriedProduct" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'InventoriedProduct',
			[
				'description' => __( 'A product with stock information.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines fields of "InventoriedProduct".
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'                => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			],
			'databaseId'        => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			],
			'manageStock'       => [
				'type'        => 'ManageStockEnum',
				'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
			],
			'lowStockAmount'    => [
				'type'        => 'Int',
				'description' => __( 'Low stock amount', 'wp-graphql-woocommerce' ),
			],
			'stockQuantity'     => [
				'type'        => 'Int',
				'description' => __( 'Number of items available for sale', 'wp-graphql-woocommerce' ),
			],
			'backorders'        => [
				'type'        => 'BackordersEnum',
				'description' => __( 'Product backorders status', 'wp-graphql-woocommerce' ),
			],
			'soldIndividually'  => [
				'type'        => 'Boolean',
				'description' => __( 'If should be sold individually', 'wp-graphql-woocommerce' ),
			],
			'backordersAllowed' => [
				'type'        => 'Boolean',
				'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
			],
			'stockStatus'       => [
				'type'        => 'StockStatusEnum',
				'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
			],
		];
	}
}
