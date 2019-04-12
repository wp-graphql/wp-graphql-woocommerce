<?php
/**
 * WPEnum Type - TaxClassEnum
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class Tax_Class
 */
class Tax_Class {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = array(
			WPEnumType::get_safe_name( 'inherit cart' ) => array(
				'value'       => 'inherit',
				'description' => __( 'Inherits Tax class from cart', 'wp-graphql-woocommerce' ),
			),
			WPEnumType::get_safe_name( 'standard' )     => array(
				'value'       => 'standard',
				'description' => __( 'Standard Tax rate', 'wp-graphql-woocommerce' ),
			),
		);

		$classes = \WC_Tax::get_tax_classes();
		foreach ( $classes as $class ) {
			$values[ WPEnumType::get_safe_name( $class ) ] = array( 'value' => sanitize_title( $class ) );
		}

		register_graphql_enum_type(
			'TaxClassEnum',
			array(
				'description' => __( 'Tax class enumeration', 'wp-graphql' ),
				'values'      => $values,
			)
		);
	}
}
