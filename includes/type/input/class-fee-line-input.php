<?php
/**
 * WPInputObjectType - FeeLineInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Fee_Line_Input
 */
class Fee_Line_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'FeeLineInput',
			[
				'description' => static function () {
					return __( 'Fee line data.', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'        => [
						'type'        => 'ID',
						'description' => static function () {
							return __( 'Fee Line ID', 'graphql-for-ecommerce' );
						},
					],
					'name'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Fee name.', 'graphql-for-ecommerce' );
						},
					],
					'amount'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Fee amount.', 'graphql-for-ecommerce' );
						},
					],
					'taxClass'  => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Tax class of fee.', 'graphql-for-ecommerce' );
						},
					],
					'taxStatus' => [
						'type'        => 'TaxStatusEnum',
						'description' => static function () {
							return __( 'Tax status of fee.', 'graphql-for-ecommerce' );
						},
					],
					'total'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line total (after discounts).', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
