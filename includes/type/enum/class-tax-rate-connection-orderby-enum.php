<?php
/**
 * WPEnum Type - TaxRateConnectionOrderbyInput
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

/**
 * Class Tax_Rate_Connection_Orderby_Enum
 */
class Tax_Rate_Connection_Orderby_Enum {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_enum_type(
			'TaxRateConnectionOrderbyEnum',
			array(
				'description' => __( 'Field to order the connection by', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'ID'    => array( 'value' => 'id' ),
					'ORDER' => array( 'value' => 'order' ),
				),
			)
		);
	}
}
