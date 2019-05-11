<?php
/**
 * WPEnum Type - ManageStockEnum
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

/**
 * Class Manage_Stock
 */
class Manage_Stock {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = [
			'TRUE'   => array( 'value' => true ),
			'FALSE'  => array( 'value' => false ),
			'PARENT' => array( 'value' => 'parent' ),
		];

		register_graphql_enum_type(
			'ManageStockEnum',
			array(
				'description' => __( 'Product manage stock enumeration', 'wp-graphql' ),
				'values'      => $values,
			)
		);
	}
}
