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
	 */
	public static function register() {
		$values = array(
			'IN_STOCK'     => array( 'value' => 'instock' ),
			'OUT_OF_STOCK' => array( 'value' => 'outofstock' ),
			'ON_BACKORDER' => array( 'value' => 'onbackorder' ),
		);

		register_graphql_enum_type(
			'StockStatusEnum',
			array(
				'description' => __( 'Product stock status enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
