<?php
/**
 * WPEnum Type - ProductTypesEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.3
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Product_Types
 */
class Product_Types {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$values = apply_filters(
			'graphql_product_types_enum_values',
			array(
				'SIMPLE'    => array(
					'value'       => 'simple',
					'description' => __( 'A simple product', 'wp-graphql-woocommerce' ),
				),
				'GROUPED'   => array(
					'value'       => 'grouped',
					'description' => __( 'A product group', 'wp-graphql-woocommerce' ),
				),
				'EXTERNAL'  => array(
					'value'       => 'external',
					'description' => __( 'An external product', 'wp-graphql-woocommerce' ),
				),
				'VARIABLE'  => array(
					'value'       => 'variable',
					'description' => __( 'A variable product', 'wp-graphql-woocommerce' ),
				),
				'VARIATION' => array(
					'value'       => 'variation',
					'description' => __( 'A product variation', 'wp-graphql-woocommerce' ),
				),

			)
		);

		if ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
			$values['UNSUPPORTED'] = array(
				'value'       => 'unsupported',
				'description' => __( 'An unsupported product', 'wp-graphql-woocommerce' ),
			);
		}

		register_graphql_enum_type(
			'ProductTypesEnum',
			array(
				'description' => __( 'Product type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
