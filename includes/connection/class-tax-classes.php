<?php
/**
 * Connection - Tax_Classes
 *
 * Registers connections to TaxClass
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Tax_Class_Connection_Resolver;

/**
 * Class - TaxClass
 */
class Tax_Classes {
	/**
	 * Registers the various connections from other Types to TaxClass
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
				'toType'         => 'TaxClass',
				'fromFieldName'  => 'taxClasses',
				'connectionArgs' => [],
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Tax_Class_Connection_Resolver( $source, $args, $context, $info );

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
