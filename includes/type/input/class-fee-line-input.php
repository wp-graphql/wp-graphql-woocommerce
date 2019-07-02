<?php
/**
 * WPInputObjectType - FeeLineInput
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPInputObject;

/**
 * Class Fee_Line_Input
 */
class Fee_Line_Input {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'FeeLineInput',
			array(
				'description' => __( 'Fee line data.', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'name'      => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Fee name.', 'wp-graphql-woocommerce' ),
					),
					'taxClass'  => array(
						'type'        => array( 'non_null' => 'TaxClassEnum' ),
						'description' => __( 'Tax class of fee.', 'wp-graphql-woocommerce' ),
					),
					'taxStatus' => array(
						'type'        => array( 'non_null' => 'TaxStatusEnum' ),
						'description' => __( 'Tax status of fee.', 'wp-graphql-woocommerce' ),
					),
					'total'     => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Line total (after discounts).', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
