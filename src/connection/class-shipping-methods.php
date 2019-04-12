<?php
/**
 * Connection - Shipping_Methods
 *
 * Registers connections to ShippingMethod
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Shipping_Methods
 */
class Shipping_Methods {
	/**
	 * Registers the various connections from other Types to TaxRate
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
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
			'toType'         => 'ShippingMethod',
			'fromFieldName'  => 'shippingMethods',
			'connectionArgs' => array(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_shipping_method( $id );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_shipping_method_connection( $source, $args, $context, $info );
			},
		);
		return array_merge( $defaults, $args );
	}
}
