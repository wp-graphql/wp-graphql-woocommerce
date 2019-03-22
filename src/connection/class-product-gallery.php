<?php

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Connection\PostObjects;

/**
 * Class Product_Gallery
 *
 * This class organizes the registration of connections from Products to MediaItems
 *
 * @package WPGraphQL\Connection
 */
class Product_Gallery {


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
			'toType'         => 'MediaItem',
			'fromFieldName'  => 'galleryImages',
			'connectionArgs' => PostObjects::get_connection_args(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return DataSource::resolve_post_objects_connection( $root, $args, $context, $info, 'attachment' );
			},
		);

		return array_merge( $defaults, $args );
	}
}
