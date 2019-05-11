<?php
/**
 * ConnectionResolver - WC_Terms_Connection_Resolver
 *
 * Resolvers connections to WooCommerce Terms (ProductCategory & ProductTags)
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Model\Coupon;
use WPGraphQL\Extensions\WooCommerce\Model\Product;

/**
 * Class WC_Terms_Connection_Resolver
 */
class WC_Terms_Connection_Resolver {
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
	public static function get_query_args( $query_args = array(), $source, $args, $context, $info ) {
		/**
		 * Determine where we're at in the Graph and adjust the query context appropriately.
		 */
		if ( true === is_object( $source ) ) {
			switch ( true ) {
				case is_a( $source, Coupon::class ):
					// @codingStandardsIgnoreLine
					if ( 'excludedProductCategories' === $info->fieldName ) {
						$query_args['term_taxonomy_id'] = $source->excluded_product_category_ids;
					} else {
						$query_args['term_taxonomy_id'] = $source->product_category_ids;
					}
					break;
				case is_a( $source, Product::class ):
					// @codingStandardsIgnoreLine
					if ( 'categories' === $info->fieldName ) {
						$query_args['term_taxonomy_id'] = $source->category_ids;
					// @codingStandardsIgnoreLine
					} elseif ( 'tags' === $info->fieldName ) {
						$query_args['term_taxonomy_id'] = $source->tag_ids;
					}
					break;
				default:
					break;
			}
		}

		$query_args = apply_filters(
			'graphql_wc_terms_connection_query_args',
			$query_args,
			$source,
			$args,
			$context,
			$info
		);

		return $query_args;
	}
}
