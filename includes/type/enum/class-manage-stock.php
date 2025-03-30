<?php
/**
 * WPEnum Type - ManageStockEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Manage_Stock
 */
class Manage_Stock {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$values = [
			'TRUE'   => [ 'value' => true ],
			'FALSE'  => [ 'value' => false ],
			'PARENT' => [ 'value' => 'parent' ],
		];

		register_graphql_enum_type(
			'ManageStockEnum',
			[
				'description' => __( 'Product manage stock enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
