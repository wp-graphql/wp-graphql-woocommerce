<?php
/**
 * WPEnum Type - BackordersEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Backorders
 */
class Backorders {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = array(
			'NO'     => array( 'value' => 'no' ),
			'NOTIFY' => array( 'value' => 'notify' ),
			'YES'    => array( 'value' => 'yes' ),
		);

		register_graphql_enum_type(
			'BackordersEnum',
			array(
				'description' => __( 'Product backorder enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
