<?php
/**
 * WPEnum Type - CurrencyEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.19.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Currency_Enum
 */
class Currency_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$currencies = get_woocommerce_currencies();
		array_walk(
			$currencies,
			static function ( &$name, $code ) {
				$name = [
					'name'        => $code,
					'value'       => $code,
					'description' => $name,
				];
			}
		);

		register_graphql_enum_type(
			'CurrencyEnum',
			[
				'description' => __( 'Currencies enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $currencies,
			]
		);
	}
}
