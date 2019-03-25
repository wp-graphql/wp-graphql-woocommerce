<?php
/**
 * Connection type - ProductCategories
 *
 * Registers connections to ProductCategory
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Product_Categories
 */
class Product_Categories {
	/**
	 * Registers the various connections from other Types to Coupons
	 */
	public static function register_connections() {
		register_graphql_connection( self::get_connection_config() );
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'productCategories',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProductCategories',
				)
			)
		);
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args Connection configuration
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = array() ) {
		$defaults = array(
			'fromType'       => 'Product',
			'toType'         => 'ProductCategory',
			'fromFieldName'  => 'categories',
			'connectionArgs' => array(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return Factory::resolve_product_category_connection( $root, $args, $context, $info );
			},
		);

		return array_merge( $defaults, $args );
	}
}
