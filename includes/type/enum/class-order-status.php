<?php
/**
 * WPEnum Type - OrderStatusEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Order_Status
 */
class Order_Status {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = array(
			'PENDING'    => array( 'value' => 'pending' ),
			'PROCESSING' => array( 'value' => 'processing' ),
			'ON_HOLD'    => array( 'value' => 'on-hold' ),
			'COMPLETED'  => array( 'value' => 'completed' ),
			'CANCELLED'  => array( 'value' => 'cancelled' ),
			'REFUNDED'   => array( 'value' => 'refunded' ),
			'FAILED'     => array( 'value' => 'failed' ),
		);

		register_graphql_enum_type(
			'OrderStatusEnum',
			array(
				'description' => __( 'Order status enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			)
		);
	}
}
