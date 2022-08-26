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
		$values = [
			'IN_STOCK'     => [ 'value' => 'instock' ],
			'OUT_OF_STOCK' => [ 'value' => 'outofstock' ],
			'ON_BACKORDER' => [ 'value' => 'onbackorder' ],
		];

		register_graphql_enum_type(
			'StockStatusEnum',
			[
				'description' => __( 'Product stock status enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
