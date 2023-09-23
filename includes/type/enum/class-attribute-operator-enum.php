<?php
/**
 * WPEnum Type - AttributeOperatorEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.18.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Attribute_Operator_Enum
 */
class Attribute_Operator_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'AttributeOperatorEnum',
			[
				'description' => __( 'Collection statistic attributes operators', 'wp-graphql-woocommerce' ),
				'values'      => [
					'IN'     => [ 'value' => 'IN' ],
					'NOT_IN' => [ 'value' => 'NOT IN' ],
					'AND'    => [ 'value' => 'AND' ],
				],
			]
		);
	}
}
