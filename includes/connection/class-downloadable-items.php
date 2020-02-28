<?php
/**
 * Connection - Downloadable_Items
 *
 * Registers connections to DownloadableItem
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since   0.4.0
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Downloadable_Items
 */
class Downloadable_Items {

	/**
	 * Registers the various connections from other Types to DownloadableItem
	 */
	public static function register_connections() {
		// From Order.
		register_graphql_connection( self::get_connection_config() );

		// From Customer.
		register_graphql_connection(
			self::get_connection_config(
				array( 'fromType' => 'Customer' )
			)
		);
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
				'fromType'       => 'Order',
				'toType'         => 'DownloadableItem',
				'fromFieldName'  => 'downloadableItems',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					return Factory::resolve_downloadable_item_connection( $source, $args, $context, $info );
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
			'active'                => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit results to downloadable items that can be downloaded now.', 'wp-graphql-woocommerce' ),
			),
			'expired'               => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit results to downloadable items that are expired.', 'wp-graphql-woocommerce' ),
			),
			'hasDownloadsRemaining' => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit results to downloadable items that have downloads remaining.', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
