<?php
/**
 * Connection - Coupons
 *
 * Registers connections to Coupon
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Coupons
 */
class Coupons extends WC_Connection {
	/**
	 * Registers the various connections from other Types to Coupon
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );

		// From Cart.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Cart',
					'fromFieldName' => 'appliedCoupons',
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
			'toType'         => 'Coupon',
			'fromFieldName'  => 'coupons',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_crud_object( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_coupon_connection( $source, $args, $context, $info );
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
				'code' => array(
					'type'        => 'String',
					'description' => __( 'Limit result set to resources with a specific code.', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}
}
