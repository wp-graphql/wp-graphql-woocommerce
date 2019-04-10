<?php
/**
 * WPInputObjectType - WCConnectionOrderbyInput
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPInputObject
 * @since   0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPInputObject;

/**
 * Class WC_Connection_Orderby_Input
 */
class WC_Connection_Orderby_Input {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'WCConnectionOrderbyInput',
			array(
				'description' => __( 'Options for ordering the connection', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'field' => array(
						'type' => array( 'non_null' => 'WCConnectionOrderbyEnum' ),
					),
					'order' => array(
						'type' => 'OrderEnum',
					),
				),
			)
		);
	}
}

