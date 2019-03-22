<?php

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Product_Tags
 *
 * This class organizes the registration of connections to ProductTags
 *
 * @package WPGraphQL\Connection
 */
class Product_Tags {


	/**
	 * Registers the various connections from other Types to Coupons
	 */
	public static function register_connections() {
		 /**
		 * Type connections
		 */
		register_graphql_connection( self::get_connection_config() );
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
		$defaults = array(
			'fromType'       => 'Product',
			'toType'         => 'ProductTag',
			'fromFieldName'  => 'tags',
			'connectionArgs' => array(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return Factory::resolve_product_tag_connection( $root, $args, $context, $info );
			},
		);

		return array_merge( $defaults, $args );
	}
}
