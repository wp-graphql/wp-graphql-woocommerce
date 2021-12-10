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
	 */
	public static function register() {
		register_graphql_input_type(
			'FeeLineInput',
			array(
				'description' => __( 'Fee line data.', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'        => array(
						'type'        => 'ID',
						'description' => __( 'Fee Line ID', 'wp-graphql-woocommerce' ),
					),
					'name'      => array(
						'type'        => 'String',
						'description' => __( 'Fee name.', 'wp-graphql-woocommerce' ),
					),
					'amount'    => array(
						'type'        => 'String',
						'description' => __( 'Fee amount.', 'wp-graphql-woocommerce' ),
					),
					'taxClass'  => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Tax class of fee.', 'wp-graphql-woocommerce' ),
					),
					'taxStatus' => array(
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Tax status of fee.', 'wp-graphql-woocommerce' ),
					),
					'total'     => array(
						'type'        => 'String',
						'description' => __( 'Line total (after discounts).', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
