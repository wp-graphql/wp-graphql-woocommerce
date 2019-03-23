<?php
/**
 * Connection resolver - WCTerms
 * 
 * Resolvers connections to WooCommerce Terms (ProductCategory & ProductTags)
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data;

use WPGraphQL\Data\TermObjectConnectionResolver;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class WC_Term_Connection_Resolver
 */
class WC_Term_Connection_Resolver extends TermObjectConnectionResolver {
	/**
	 * TermObjectConnectionResolver constructor.
	 *
	 * @param $taxonomy
	 */
	public function __construct( $taxonomy ) {
		self::$taxonomy = $taxonomy;
	}

	/**
	 * Returns an array of query_args to use in the WP_Term_Query to fetch the necessary terms for
	 * the connection
	 *
	 * @param             $source
	 * @param array       $args
	 * @param AppContext  $context
	 * @param ResolveInfo $info
	 *
	 * @return array
	 */
	public static function get_query_args( $source, array $args, AppContext $context, ResolveInfo $info ) {
		/**
		 * Set the taxonomy for the $args
		 */
		$query_args['taxonomy'] = self::$taxonomy;

		/**
		 * Prepare for later use
		 */
		$last  = ! empty( $args['last'] ) ? $args['last'] : null;
		$first = ! empty( $args['first'] ) ? $args['first'] : null;

		/**
		 * Set the default parent for TermObject Queries to be "0" to only get top level terms, unless
		 * includeChildren is set
		 */
		// $query_args['parent'] = 0;

		/**
		 * Set hide_empty as false by default
		 */
		$query_args['hide_empty'] = false;

		/**
		 * Set the number, ensuring it doesn't exceed the amount set as the $max_query_amount
		 */
		$query_args['number'] = min( max( absint( $first ), absint( $last ), 10 ), self::get_query_amount( $source, $args, $context, $info ) ) + 1;

		/**
		 * Orderby Name by default
		 */
		$query_args['orderby'] = 'name';

		/**
		 * Take any of the $args that were part of the GraphQL query and map their
		 * GraphQL names to the WP_Term_Query names to be used in the WP_Term_Query
		 */
		$input_fields = array();
		if ( ! empty( $args['where'] ) ) {
			$input_fields = self::sanitize_input_fields( $args['where'], $source, $args, $context, $info );
		}

		/**
		 * Merge the default $query_args with the $args that were entered
		 * in the query.
		 */
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['order'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'DESC' : 'ASC';
		}

		/**
		 * Set the graphql_cursor_offset
		 */
		$query_args['graphql_cursor_offset']  = self::get_offset( $args );
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $args;

		/**
		 * Determine where we're at in the Graph and adjust the query context appropriately.
		 */
		if ( true === is_object( $source ) ) {
			switch ( true ) {
				case $source instanceof \WC_Product:
					if ( 'product_tag' === self::$taxonomy ) {
						$query_args['object_ids'] = $source->get_tag_ids();
					} else {
						$query_args['term_taxonomy_id'] = $source->get_category_ids();
					}
					break;
				case $source instanceof \WC_Coupon:
					if ( 'excludedProductCategories' === $info->fieldName ) {
						$query_args['term_taxonomy_id'] = $source->get_excluded_product_categories();
					} else {
						$query_args['term_taxonomy_id'] = $source->get_product_categories();
					}
					break;
				default:
					break;
			}
		}

		$query_args = apply_filters( 'graphql_wc_term_object_connection_query_args', $query_args, $source, $args, $context, $info );
		return $query_args;
	}
}
