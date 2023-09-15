<?php
/**
 * Defines the fields for manage product inventories.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Inventoried_Products
 */
class Inventoried_Products {
	/**
	 * Registers the "InventoriedProducts" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'InventoriedProducts',
			[
				'description' => __( 'Products with stock information.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines "InventoriedProducts" fields.
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
				'type'        => 'Boolean',
				'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
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
