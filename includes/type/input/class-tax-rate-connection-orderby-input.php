<?php
/**
 * WPInputObjectType - TaxRateConnectionOrderbyInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Tax_Rate_Connection_Orderby_Input
 */
class Tax_Rate_Connection_Orderby_Input {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'TaxRateConnectionOrderbyInput',
			array(
				'description' => __( 'Options for ordering the connection', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'field' => array(
						'type' => array( 'non_null' => 'TaxRateConnectionOrderbyEnum' ),
					),
					'order' => array(
						'type' => 'OrderEnum',
					),
				),
			)
		);
	}
}
