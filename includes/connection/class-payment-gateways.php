<?php
/**
 * Connection - PaymentGateways
 *
 * Registers connections to PaymentGateway
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Payment_Gateway_Connection_Resolver;

/**
 * Class - PaymentGateways
 */
class Payment_Gateways {
	/**
	 * Registers the various connections from other Types to Customer.
	 *
	 * @return void
	 */
	public static function register_connections() {
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
				'toType'         => 'PaymentGateway',
				'fromFieldName'  => 'paymentGateways',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Payment_Gateway_Connection_Resolver();

					return $resolver->resolve( $source, $args, $context, $info );
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
		return [
			'all' => [
				'type'        => 'Boolean',
				'description' => __( 'Include disabled payment gateways?', 'wp-graphql-woocommerce' ),
			],
		];
	}
}
