<?php
/**
 * WPEnum Type - PricingFieldFormatEnum
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.1.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

/**
 * Class Pricing_Field_Format
 */
class Pricing_Field_Format {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = array(
			'FORMATTED' => array( 'value' => 'formatted' ),
			'RAW'       => array( 'value' => 'raw' ),
		);

		register_graphql_enum_type(
			'PricingFieldFormatEnum',
			array(
				'description' => __( 'Pricing field format enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
