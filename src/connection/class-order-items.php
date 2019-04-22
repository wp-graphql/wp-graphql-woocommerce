<?php
/**
 * Connection - Order_Items
 *
 * Registers connections to OrderItem
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Order_Items
 */
class Order_Items {
	/**
	 * Registers connection
	 */
	public static function register_connections() {
		// From Order.
		register_graphql_connection( self::get_connection_config() );
		register_graphql_connection(
			self::get_connection_config(
				array(
					'toType'        => 'TaxLine',
					'fromFieldName' => 'taxLines',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'toType'        => 'ShippingLine',
					'fromFieldName' => 'shippingLines',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'toType'        => 'FeeLine',
					'fromFieldName' => 'feeLines',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'toType'        => 'FeeLine',
					'toType'        => 'CouponLine',
					'fromFieldName' => 'couponLines',
				)
			)
		);

		// From Refund.
		register_graphql_connection( self::get_connection_config( array( 'fromType' => 'Refund' ) ) );
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args - Connection configuration.
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = [] ) {
		$defaults = array(
			'fromType'       => 'Order',
			'toType'         => 'LineItem',
			'fromFieldName'  => 'lineItems',
			'connectionArgs' => array(),
			'resolveNode'    => function( $item, $args, $context, $info ) {
				return Factory::resolve_order_item( $item );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_order_item_connection( $source, $args, $context, $info );
			},
		);
		return array_merge( $defaults, $args );
	}
}
