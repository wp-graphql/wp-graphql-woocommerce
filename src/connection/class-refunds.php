<?php
/**
 * Connection - Refunds
 *
 * Registers connections to Refund
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Refunds
 */
class Refunds extends WC_Connection {
	/**
	 * Registers the various connections from other Types to Refund
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
		// From Order.
		register_graphql_connection(
			self::get_connection_config(
				array( 'fromType' => 'Order' )
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array( 'fromType' => 'Customer' )
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
	public static function get_connection_config( $args = array() ) {
		$defaults = array(
			'fromType'       => 'RootQuery',
			'toType'         => 'Refund',
			'fromFieldName'  => 'refunds',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_crud_object( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_refund_connection( $source, $args, $context, $info );
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
				'statuses' => array(
					'type'        => array( 'list_of' => 'String' ),
					'description' => __( 'Limit result set to refunds assigned a specific status.', 'wp-graphql-woocommerce' ),
				),
				'orderIn'  => array(
					'type'        => array( 'list_of' => 'Int' ),
					'description' => __( 'Limit result set to refunds from a specific group of order IDs.', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}
}
