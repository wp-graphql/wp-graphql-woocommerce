<?php

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Coupons
 *
 * This class organizes the registration of connections to Coupons
 *
 * @package WPGraphQL\Connection
 */
class Coupons {


	/**
	 * Registers the various connections from other Types to Coupon
	 */
	public static function register_connections() {
		 /**
		 * Register Connections to Coupons
		 */
		register_graphql_connection( self::get_connection_config() );
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = array() ) {
		$defaults = array(
			'fromType'       => 'RootQuery',
			'toType'         => 'Coupon',
			'fromFieldName'  => 'coupons',
			'connectionArgs' => self::get_connection_args(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return Factory::resolve_coupon_connection( $root, $args, $context, $info );
			},
		);

		return array_merge( $defaults, $args );
	}

	/**
	 * This returns the connection args for the Coupon connection
	 *
	 * @access public
	 * @return array
	 */
	public static function get_connection_args() {
		return array(
			'code' => array(
				'type'        => 'String',
				'description' => __( 'Coupon code', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
