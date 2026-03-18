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
				'description' => __( 'Fee line data.', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'name'     => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Unique name for the fee.', 'wp-graphql-woocommerce' ),
					],
					'amount'   => [
						'type'        => 'Float',
						'description' => __( 'Fee amount', 'wp-graphql-woocommerce' ),
					],
					'taxable'  => [
						'type'        => 'Boolean',
						'description' => __( 'Is the fee taxable?', 'wp-graphql-woocommerce' ),
					],
					'taxClass' => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'The tax class for the fee if taxable.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
