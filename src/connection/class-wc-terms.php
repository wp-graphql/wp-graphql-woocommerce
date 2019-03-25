<?php
/**
 * Connection type - ProductTags
 *
 * Registers connections to ProductTags
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Connection\TermObjects;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - WC_Terms
 */
class WC_Terms {
	public static function register_connections() {
		/**
		 * To product category connections
		 */
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'productCategories',
				),
				'product_cat'
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'excludedProductCategories',
				),
				'product_cat'
			)
		);
	}

	public static function get_connection_config( $args, $taxonomy_name ) {
		$defaults = [
			'queryClass'       => 'WP_Term_Query',
			'connectionArgs'   => TermObjects::get_connection_args(),
			'resolveNode'      => function( $id, $args, $context, $info ) {
				return DataSource::resolve_term_object( $id, $context );
			},
			'resolve'          => function ( $root, $args, $context, $info ) use ( $taxonomy_name ) {
				return Factory::resolve_wc_terms_connection( $root, $args, $context, $info, $taxonomy_name );
			}
		];
		return array_merge( $defaults, $args );
	}

	public static function get_connection_args() {
		
	}
}