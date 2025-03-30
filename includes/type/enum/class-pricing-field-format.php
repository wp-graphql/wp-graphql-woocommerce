<?php
/**
 * WPEnum Type - PricingFieldFormatEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.1.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Pricing_Field_Format
 */
class Pricing_Field_Format {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$values = [
			'FORMATTED' => [ 'value' => 'formatted' ],
			'RAW'       => [ 'value' => 'raw' ],
		];

		register_graphql_enum_type(
			'PricingFieldFormatEnum',
			[
				'description' => __( 'Pricing field format enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
