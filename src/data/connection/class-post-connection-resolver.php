<?php
/**
 * Connection resolver - Post
 *
 * Filters connections to WordPress post-types
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Model\Product;

/**
 * Class Post_Connection_Resolver
 */
class Post_Connection_Resolver {
	/**
	 * This prepares the $query_args for use in the connection query. This is where default $args are set, where dynamic
	 * $args from the $this->source get set, and where mapping the input $args to the actual $query_args occurs.
	 *
	 * @param array       $query_args - WP_Query args.
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return mixed
	 */
	public static function get_query_args( $query_args, $source, $args, $context, $info ) {
		// Determine where we're at in the Graph and adjust the query context appropriately.
		if ( true === is_object( $source ) ) {
			$query_args['post_parent'] = 0;
			unset( $query_args['post__in'] );
			switch ( true ) {
				case is_a( $source, Product::class ):
					// @codingStandardsIgnoreLine
					if ( 'galleryImages' === $info->fieldName ) {
						unset( $query_args['post_parent'] );
						$query_args['post__in'] = $source->gallery_image_ids;
					}
					break;
				default:
					break;
			}
		}

		$query_args = apply_filters(
			'graphql_wc_posts_connection_query_args',
			$query_args,
			$source,
			$args,
			$context,
			$info
		);

		return $query_args;
	}
}
