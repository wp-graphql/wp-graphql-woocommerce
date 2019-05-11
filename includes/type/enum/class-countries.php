<?php
/**
 * WPEnum Type - CountriesEnum
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

/**
 * Class Countries
 */
class Countries {
	/**
	 * Registers type
	 */
	public static function register() {
		$countries = \WC()->countries->get_countries();
		$values    = array_map(
			function( $value ) {
				return array( 'value' => $value );
			},
			$countries
		);

		register_graphql_enum_type(
			'CountriesEnum',
			array(
				'description' => __( 'Countries enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
