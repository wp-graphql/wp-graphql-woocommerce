<?php
/**
 * Connection - Shipping_Methods
 *
 * Registers connections to ShippingMethod
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

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
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		return array_merge(
			array(
				'fromType'       => 'RootQuery',
				'toType'         => 'ShippingMethod',
				'fromFieldName'  => 'shippingMethods',
				'connectionArgs' => array(),
				'resolveNode'    => function( $id ) {
					return Factory::resolve_shipping_method( $id );
				},
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					return Factory::resolve_shipping_method_connection( $source, $args, $context, $info );
				},
			),
			$args
		);
	}
}
