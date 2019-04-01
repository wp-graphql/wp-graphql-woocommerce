<?php
/**
 * Connection type - Posts
 *
 * Registers connections to Posts
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Connection\PostObjects;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Posts
 */
class Posts extends PostObjects {
	/**
	 * Registers the various connections from other WooCommerce Types to other WordPress post-types
	 */
	public static function register_connections() {
		/**
		 * From Product to MediaItem
		 */
		register_graphql_connection(
			self::get_connection_config(
				get_post_type_object( 'attachment' ),
				array(
					'fromType'      => 'Product',
					'toType'        => 'MediaItem',
					'fromFieldName' => 'galleryImages',
				)
			)
		);
	}
}
