<?php
/**
 * Connection - Orders
 *
 * Registers connections to Order
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Orders
 */
class Orders extends WC_Connection {
	/**
	 * Registers the various connections from other Types to Customer
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
		// From Customer.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Customer',
					'fromFieldName' => 'orders',
				)
			)
		);
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
			'fromType'       => 'RootQuery',
			'toType'         => 'Order',
			'fromFieldName'  => 'orders',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_crud_object( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_order_connection( $source, $args, $context, $info );
			},
		);
		return array_merge( $defaults, $args );
	}

	/**
	 * Returns array of where args
	 *
	 * @return array
	 */
	public static function get_connection_args() {
		return array_merge(
			self::get_shared_connection_args(),
			array(
				'statuses'    => array(
					'type'        => array( 'list_of' => 'OrderStatusEnum' ),
					'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
				),
				'customerId'  => array(
					'type'        => 'Int',
					'description' => __( 'Limit result set to orders assigned a specific customer.', 'wp-graphql-woocommerce' ),
				),
				'customersIn' => array(
					'type'        => array( 'list_of' => 'Int' ),
					'description' => __( 'Limit result set to orders assigned a specific group of customers.', 'wp-graphql-woocommerce' ),
				),
				'productId'   => array(
					'type'        => 'Int',
					'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}
}
