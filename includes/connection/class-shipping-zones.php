<?php
/**
 * Connection - Shipping_Zones
 *
 * Registers connections to ShippingZone
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Shipping_Zone_Connection_Resolver;

/**
 * Class - Shipping_Zones
 */
class Shipping_Zones {
	/**
	 * Registers the various connections from other Types to ShippingZone
	 *
	 * @return void
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
	public static function get_connection_config( $args = [] ): array {
		return array_merge(
			[
				'fromType'       => 'RootQuery',
				'toType'         => 'ShippingZone',
				'fromFieldName'  => 'shippingZones',
				'connectionArgs' => [],
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Shipping_Zone_Connection_Resolver( $source, $args, $context, $info );

					return $resolver->get_connection();
				},
			],
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		return [];
	}
}
