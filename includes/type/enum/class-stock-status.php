<?php
/**
 * WPEnum Type - StockStatusEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Stock_Status
 */
class Stock_Status {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'StockStatusEnum',
			array(
				'description' => __( 'Product stock status enumeration', 'wp-graphql-woocommerce' ),
				'values'      => self::get_stock_statuses(),
			)
		);
	}

	/**
	 * Returns WooCommerce stock status values to be exposed to the GraphQL schema.
	 *
	 * @return array
	 */
	private static function get_stock_statuses() {
		return apply_filters(
			'graphql_woocommerce_product_stock_statuses',
			array(
				'IN_STOCK'     => array( 'value' => 'instock' ),
				'OUT_OF_STOCK' => array( 'value' => 'outofstock' ),
				'ON_BACKORDER' => array( 'value' => 'onbackorder' ),
			)
		);
	}
}
