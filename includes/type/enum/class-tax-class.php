<?php
/**
 * WPEnum Type - TaxClassEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class Tax_Class
 */
class Tax_Class {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$values = [
			'INHERIT_CART' => [
				'value'       => 'inherit',
				'description' => __( 'Inherits Tax class from cart', 'wp-graphql-woocommerce' ),
			],
			'STANDARD'     => [
				'value'       => '',
				'description' => __( 'Standard Tax rate', 'wp-graphql-woocommerce' ),
			],
		];

		$classes = \WC_Tax::get_tax_classes();
		foreach ( $classes as $class ) {
			$values[ WPEnumType::get_safe_name( $class ) ] = [ 'value' => sanitize_title( $class ) ];
		}

		register_graphql_enum_type(
			'TaxClassEnum',
			[
				'description' => __( 'Tax class enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
