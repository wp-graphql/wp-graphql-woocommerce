<?php
/**
 * WPInputObjectType - FeeInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   TBD
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
					return __( 'Fee line data.', 'wp-graphql-woocommerce' );
				},
				'fields'      => [
					'name'     => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
					return __( 'Unique name for the fee.', 'wp-graphql-woocommerce' );
				},
					],
					'amount'   => [
						'type'        => 'Float',
						'description' => static function () {
					return __( 'Fee amount', 'wp-graphql-woocommerce' );
				},
					],
					'taxable'  => [
						'type'        => 'Boolean',
						'description' => static function () {
					return __( 'Is the fee taxable?', 'wp-graphql-woocommerce' );
				},
					],
					'taxClass' => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
					return __( 'The tax class for the fee if taxable.', 'wp-graphql-woocommerce' );
				},
					],
				],
			]
		);
	}
}
