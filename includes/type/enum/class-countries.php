<?php
/**
 * WPEnum Type - CountriesEnum
 *
 * @package \WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.1.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Countries
 */
class Countries {
	/**
	 * Registers type
	 */
	public static function register() {
		$countries = \WC()->countries->get_countries();
		array_walk(
			$countries,
			function( &$value, $code ) {
				$value = array( 'value' => $code );
			}
		);

		register_graphql_enum_type(
			'CountriesEnum',
			array(
				'description' => __( 'Countries enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $countries,
			)
		);
	}
}
