<?php
/**
 * Connection type - Customers
 *
 * Registers connections to Customers
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Customers
 */
class Customers {
	/**
	 * Registers the various connections from other Types to Customer
	 */
	public static function register_connections() {
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'RootQuery',
					'toType'        => 'Customer',
					'fromFieldName' => 'customers',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'Customer',
					'fromFieldName' => 'usedBy',
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
	public static function get_connection_config( $args ) {
		$defaults = array(
			'connectionArgs' => array(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_customer( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_customer_connection( $source, $args, $context, $info );
			},
		);
		return array_merge( $defaults, $args );
	}
}
