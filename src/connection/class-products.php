<?php
/**
 * Connection type - Products
 *
 * Registers connections to Products
 *
 * @package WPGraphQL\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Products
 */
class Products {
	/**
	 * Registers the various connections from other Types to Product
	 */
	public static function register_connections() {
		/**
		 * Root connections
		 */
		register_graphql_connection( self::get_connection_config() );

		/**
		 * Taxonomy connections
		 */
		register_graphql_connection(
			self::get_connection_config(
				array( 'fromType' => 'productTag' )
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array( 'fromType' => 'productCategory' )
			)
		);

		/**
		 * Type connections
		 */
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'upsell'
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'crossSell'
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'variations'
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'products'
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProducts'
				)
			)
		);
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = array() ) {
		$defaults = [
			'fromType'       => 'RootQuery',
			'toType'         => 'Product',
			'fromFieldName'  => 'products',
			'connectionArgs' => self::get_connection_args(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return Factory::resolve_product_connection( $root, $args, $context, $info );
			},
		];

		return array_merge( $defaults, $args );
	}

	/**
	 * This returns the connection args for the Product connection
	 *
	 * @access public
	 * @return array
	 */
	public static function get_connection_args() {
		return array(
			'slug' => array(
				'type'        => 'String',
				'description' => __( 'Product slug', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
