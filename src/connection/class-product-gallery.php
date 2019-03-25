<?php
/**
 * Connection type - ProductGallery
 *
 * Registers a connection from Product to MediaItems
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Connection\PostObjects;

/**
 * Class Product_Gallery
 */
class Product_Gallery {
	/**
	 * Registers a connection from Product to MediaItem
	 */
	public static function register_connections() {
		register_graphql_connection( self::get_connection_config() );
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
