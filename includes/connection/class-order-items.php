<?php
/**
 * Connection - Order_Items
 *
 * Registers connections to OrderItem
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Order_Items
 */
class Order_Items {

	/**
	 * Registers connections.
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
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		$defaults = array(
			'fromType'       => 'Order',
			'toType'         => 'LineItem',
			'fromFieldName'  => 'lineItems',
			'connectionArgs' => array(),
			'resolveNode'    => function( $item ) {
				return Factory::resolve_order_item( $item );
			},
			'resolve'        => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
				return Factory::resolve_order_item_connection( $source, $args, $context, $info );
			},
		);

		return array_merge( $defaults, $args );
	}
}
