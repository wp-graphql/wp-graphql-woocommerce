<?php
/**
 * Resolvers connections to WooCommerce Terms.
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Coupon;
use WPGraphQL\WooCommerce\Model\Product;
use WPGraphQL\WooCommerce\Model\Crud_CPT;

/**
 * Class WC_Term_Connection_Resolver
 */
class WC_Term_Connection_Resolver {
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
		if ( is_a( $source, Crud_CPT::class ) ) {
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
					if ( 'productCategories' === $info->fieldName ) {
						$query_args['term_taxonomy_id'] = $source->category_ids;
					// @codingStandardsIgnoreLine
					} elseif ( 'productTags' === $info->fieldName ) {
						$query_args['term_taxonomy_id'] = $source->tag_ids;
					}
					break;
				default:
					break;
			}
			if ( empty( $query_args['term_taxonomy_id'] ) ) {
				$connected_items_only     = isset( $input_fields['shouldOnlyIncludeConnectedItems'] ) ? $input_fields['shouldOnlyIncludeConnectedItems'] : true;
				$query_args['object_ids'] = $source->ID;
			}

			if ( isset( $connected_items_only ) && false === $connected_items_only ) {
				unset( $query_args['object_ids'] );
			}
		}

		// NOTE: Temporary fix for querying child categories.
		// See my(@kidunot89) comments in issues [#140](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/140).
		if (
			is_a( $GLOBALS['post'], 'WP_Post' )
			&& isset( $GLOBALS['post']->ID )
			&& ( 'product_cat' === $query_args['taxonomy'] || 'product_tag' === $query_args['taxonomy'] )
			&& $source->ID !== $GLOBALS['post']->ID
		) {
			unset( $query_args['object_ids'] );
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
