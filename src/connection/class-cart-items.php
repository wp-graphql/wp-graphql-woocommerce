<?php
/**
 * Connection - Cart_Items
 *
 * Registers connections to CartItem
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since   0.0.3
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Cart_Items
 */
class Cart_Items {
	/**
	 * Registers the various connections from other Types to CartItem
	 */
	public static function register_connections() {
		// From Cart.
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
	public static function get_connection_config( $args = [] ) {
		$defaults = array(
			'fromType'       => 'Cart',
			'toType'         => 'CartItem',
			'fromFieldName'  => 'contents',
			'connectionArgs' => self::get_connection_args(),
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_cart_item_connection( $source, $args, $context, $info );
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
		return array(
			'needShipping' => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit results to cart item that require shipping', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
