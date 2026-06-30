<?php
/**
 * WPInputObjectType - FeeInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Fee_Input
 */
class Fee_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'FeeInput',
			[
				'description' => static function () {
					return __( 'Fee line data.', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'name'     => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Unique name for the fee.', 'graphql-for-ecommerce' );
						},
					],
					'amount'   => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Fee amount', 'graphql-for-ecommerce' );
						},
					],
					'taxable'  => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Is the fee taxable?', 'graphql-for-ecommerce' );
						},
					],
					'taxClass' => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'The tax class for the fee if taxable.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
