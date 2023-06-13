<?php
/**
 * WPInputObjectTypes:
 * - PostTypeOrderbyInput
 * - ProductsOrderbyInput
 * - OrdersOrderbyInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.2
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Orderby_Inputs
 */
class Orderby_Inputs {

	/**
	 * Registers Orderby WPInputObject type to schema.
	 *
	 * @param string $base_name  Base name of WPInputObject being registered.
	 *
	 * @return void
	 */
	public static function register_orderby_input( $base_name ) {
		register_graphql_input_type(
			$base_name . 'OrderbyInput',
			[
				'description' => __( 'Options for ordering the connection', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'field' => [
						'type' => [ 'non_null' => $base_name . 'OrderbyEnum' ],
					],
					'order' => [
						'type' => 'OrderEnum',
					],
				],
			]
		);
	}

	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$input_types = [
			'PostType',
			'Products',
			'Orders',
		];

		foreach ( $input_types as $name ) {
			self::register_orderby_input( $name );
		}
	}
}

