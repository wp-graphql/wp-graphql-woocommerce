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
			[
				'SIMPLE'    => [
					'value'       => 'simple',
					'description' => static function () {
						return __( 'A simple product', 'graphql-for-ecommerce' );
					},
				],
				'GROUPED'   => [
					'value'       => 'grouped',
					'description' => static function () {
						return __( 'A product group', 'graphql-for-ecommerce' );
					},
				],
				'EXTERNAL'  => [
					'value'       => 'external',
					'description' => static function () {
						return __( 'An external product', 'graphql-for-ecommerce' );
					},
				],
				'VARIABLE'  => [
					'value'       => 'variable',
					'description' => static function () {
						return __( 'A variable product', 'graphql-for-ecommerce' );
					},
				],
				'VARIATION' => [
					'value'       => 'variation',
					'description' => static function () {
						return __( 'A product variation', 'graphql-for-ecommerce' );
					},
				],

			]
		);

		if ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
			$values['UNSUPPORTED'] = [
				'value'       => 'unsupported',
				'description' => static function () {
					return __( 'An unsupported product', 'graphql-for-ecommerce' );
				},
			];
		}

		register_graphql_enum_type(
			'ProductTypesEnum',
			[
				'description' => static function () {
					return __( 'Product type enumeration', 'graphql-for-ecommerce' );
				},
				'values'      => $values,
			]
		);

		register_graphql_enum_type(
			'ProductTypesWithVariationsEnum',
			[
				'description' => static function () {
					return __( 'Product type enumeration including variation types', 'graphql-for-ecommerce' );
				},
				'values'      => apply_filters(
					'graphql_product_types_with_variations_enum_values',
					array_merge(
						$values,
						[
							'VARIATION' => [
								'value'       => 'variation',
								'description' => static function () {
									return __( 'A product variation', 'graphql-for-ecommerce' );
								},
							],
						]
					)
				),
			]
		);
	}
}
