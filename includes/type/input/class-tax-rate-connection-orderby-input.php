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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'TaxRateConnectionOrderbyInput',
			[
				'description' => __( 'Options for ordering the connection', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'field' => [
						'type' => [ 'non_null' => 'TaxRateConnectionOrderbyEnum' ],
					],
					'order' => [
						'type' => 'OrderEnum',
					],
				],
			]
		);
	}
}
