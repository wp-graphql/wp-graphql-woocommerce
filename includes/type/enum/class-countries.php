<?php
/**
 * WPEnum Type - CountriesEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.1.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Countries
 */
class Countries {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$wc_countries = new \WC_Countries();
		$countries    = $wc_countries->get_countries();
		array_walk(
			$countries,
			static function ( &$value, $code ) {
				$value = [ 'value' => $code ];
			}
		);

		register_graphql_enum_type(
			'CountriesEnum',
			[
				'description' => __( 'Countries enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $countries,
			]
		);
	}
}
