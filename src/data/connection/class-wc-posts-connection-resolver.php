<?php
/**
 * Connection resolver - WC_Posts
 *
 * Resolves connections to WC_Posts
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Term;
use WPGraphQL\Extensions\WooCommerce\Model\WC_Post;

/**
 * Class WC_Posts_Connection_Resolver
 */
class WC_Posts_Connection_Resolver {
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
		/**
		 * Determine where we're at in the Graph and adjust the query context appropriately.
		 */
		if ( true === is_object( $source ) ) {
			if ( is_a( $source, WC_Post::class ) ) {
				$query_args['post_parent'] = 0;
				unset( $query_args['post__in'] );
				if ( 'shop_order' === $source->post_type ) {
					$query_args['post_status'] = array_keys( wc_get_order_statuses() );
				}

				// @codingStandardsIgnoreStart
				switch ( $info->fieldName ) {
				// @codingStandardsIgnoreEnd
					case 'upsell':
						$query_args['post__in'] = ! empty( $source->upsell_ids ) ? $source->upsell_ids : [ '0' ];
						break;

					case 'crossSell':
						$query_args['post__in'] = ! empty( $source->cross_sell_ids ) ? $source->cross_sell_ids : [ '0' ];
						break;

					case 'variations':
						$query_args['post_parent'] = $source->ID;
						$query_args['post_type']   = 'product_variation';
						break;

					case 'galleryImages':
						unset( $query_args['post_parent'] );
						$query_args['post__in'] = ! empty( $source->gallery_image_ids ) ? $source->gallery_image_ids : [ '0' ];
						break;

					case 'products':
						$query_args['post__in'] = ! empty( $source->product_ids ) ? $source->product_ids : [ '0' ];
						break;

					case 'excludedProducts':
						$query_args['post__in'] = ! empty( $source->excluded_product_ids ) ? $source->excluded_product_ids : [ '0' ];
						break;
					default:
						break;
				}
			}
			if ( is_a( $source, Term::class ) ) {
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => $source->taxonomy,
						'terms'    => array( $source->term_id ),
						'field'    => 'term_id',
					),
				);
			}
		}

		$results = new \WP_Query( $query_args );
		var_dump( $query_args );
		var_dump( $results->posts );

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
