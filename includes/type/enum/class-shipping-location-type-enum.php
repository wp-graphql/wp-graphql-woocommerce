<?php
/**
 * WPEnum Type - ShippingLocationType
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Shipping_Location_Type_Enum
 */
class Shipping_Location_Type_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'ShippingLocationTypeEnum',
			[
				'description' => __( 'A Shipping zone location type.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'COUNTRY'   => [ 'value' => 'country' ],
					'CONTINENT' => [ 'value' => 'continent' ],
					'STATE'     => [ 'value' => 'state' ],
					'POSTCODE'  => [ 'value' => 'postcode' ],
				],
			]
		);
	}
}
