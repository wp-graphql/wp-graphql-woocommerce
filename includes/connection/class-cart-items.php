<?php
/**
 * Connection - Cart_Items
 *
 * Registers connections to CartItem
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since   0.0.3
 */

namespace WPGraphQL\WooCommerce\Connection;

use WPGraphQL\WooCommerce\Data\Factory;

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
	public static function get_connection_config( $args = array() ) {
		$defaults = array(
			'fromType'         => 'Cart',
			'toType'           => 'CartItem',
			'fromFieldName'    => 'contents',
			'connectionArgs'   => self::get_connection_args(),
			'connectionFields' => array(
				'itemCount'    => array(
					'type'        => 'Int',
					'description' => __( 'Total number of items in the cart.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						if ( empty( $source['edges'] ) ) {
							return 0;
						}

						$items = array_values( $source['edges'][0]['source']->get_cart() );
						$count = 0;
						foreach ( $items as $item ) {
							$count += $item['quantity'];
						}

						return $count;
					},
				),
				'productCount' => array(
					'type'        => 'Int',
					'description' => __( 'Total number of different products in the cart', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						if ( empty( $source['edges'] ) ) {
							return 0;
						}

						$items = array_values( $source['edges'][0]['source']->get_cart() );
						return count( $items );
					},
				),
			),
			'resolve'          => function ( $source, $args, $context, $info ) {
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
				'description' => __( 'Limit results to cart items that require shipping', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
