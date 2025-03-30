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
	 *
	 * @return void
	 */
	public static function register() {
		$values = [
			'NO'     => [ 'value' => 'no' ],
			'NOTIFY' => [ 'value' => 'notify' ],
			'YES'    => [ 'value' => 'yes' ],
		];

		register_graphql_enum_type(
			'BackordersEnum',
			[
				'description' => __( 'Product backorder enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
