<?php
/**
 * WPEnum Type - TaxRateConnectionOrderbyInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Tax_Rate_Connection_Orderby_Enum
 */
class Tax_Rate_Connection_Orderby_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'TaxRateConnectionOrderbyEnum',
			[
				'description' => __( 'Field to order the connection by', 'wp-graphql-woocommerce' ),
				'values'      => [
					'ID'    => [ 'value' => 'id' ],
					'ORDER' => [ 'value' => 'order' ],
				],
			]
		);
	}
}
