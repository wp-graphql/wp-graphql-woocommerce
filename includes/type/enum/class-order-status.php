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
	 *
	 * @return void
	 */
	public static function register() {
		$statuses = \wc_get_order_statuses();

		$values = [];
		foreach ( $statuses as $status => $description ) {
			$split_status_slug = explode( 'wc-', $status );
			$value             = array_pop( $split_status_slug );
			$key               = strtoupper( str_replace( '-', '_', $value ) );
			$values[ $key ]    = compact( 'value', 'description' );
		}

		register_graphql_enum_type(
			'OrderStatusEnum',
			[
				'description' => __( 'Order status enumeration', 'wp-graphql-woocommerce' ),
				'values'      => $values,
			]
		);
	}
}
