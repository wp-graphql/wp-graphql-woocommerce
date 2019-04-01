<?php
/**
 * Connection - Products
 *
 * Registers connections to Product
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Products
 */
class Products {
	/**
	 * Registers the various connections from other Types to Product
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
		// From Coupon.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'products',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProducts',
				)
			)
		);
		// From Product.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'upsell',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'crossSell',
				)
			)
		);

		// From Product to ProductVariation.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'toType'        => 'ProductVariation',
					'fromFieldName' => 'variations',
				)
			)
		);

		// From ProductCategory.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'ProductCategory',
					'fromFieldName' => 'products',
				)
			)
		);

		// From ProductTag.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'ProductTag',
					'fromFieldName' => 'products',
				)
			)
		);
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
			'fromType'       => 'RootQuery',
			'toType'         => 'Product',
			'fromFieldName'  => 'products',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_crud_object( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_product_connection( $source, $args, $context, $info );
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
		return array();
	}
}
