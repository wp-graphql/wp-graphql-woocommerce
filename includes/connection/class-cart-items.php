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

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
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
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		return array_merge(
			array(
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
							if ( empty( $items ) ) {
								return 0;
							}

							return array_sum( array_column( $items, 'quantity' ) );
						},
					),
					'productCount' => array(
						'type'        => 'Int',
						'description' => __( 'Total number of different products in the cart', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							if ( empty( $source['edges'] ) ) {
								return 0;
							}

							return count( array_values( $source['edges'][0]['source']->get_cart() ) );
						},
					),
				),
				'resolve'          => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					return Factory::resolve_cart_item_connection( $source, $args, $context, $info );
				},
			),
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		return array(
			'needsShipping' => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit results to cart items that require shipping', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
