<?php
/**
 * Connection type - Posts
 *
 * Registers connections to Posts
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\Type\Connection\PostObjects;

/**
 * Class - Posts
 */
class Posts extends PostObjects {
	/**
	 * Registers the various connections from other WooCommerce Types to other WordPress post-types.
	 */
	public static function register_connections() {
		register_graphql_connection(
			self::get_connection_config(
				get_post_type_object( 'attachment' ),
				[
					'fromType'      => 'Product',
					'toType'        => 'MediaItem',
					'fromFieldName' => 'galleryImages',
					'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'attachment' );
						$resolver->set_query_arg( 'post_type', 'attachment' );
						$resolver->set_query_arg( 'post__in', $source->gallery_image_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				]
			)
		);
	}
}
