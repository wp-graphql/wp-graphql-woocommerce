<?php
/**
 * Connection resolver - WC_Terms
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
use WPGraphQL\Extensions\WooCommerce\Model\WC_Post;

/**
 * Class WC_Terms_Connection_Resolver
 */
class WC_Terms_Connection_Resolver extends TermObjectConnectionResolver {
	public static function wc_query_args( $query_args = [], $source, $args, $context, $info ) {
		unset( $query_args['object_ids'] );

		/**
		 * Determine where we're at in the Graph and adjust the query context appropriately.
		 */
		if ( true === is_object( $source ) ) {
			if( is_a( $source, WC_Post::class ) ) {
				switch ( $info->fieldName ) {
					case 'productCategories':
						$query_args['term_taxonomy_id'] = $source->product_category_ids;
						break;

					case 'excludedProductCategories':
						$query_args['term_taxonomy_id'] = $source->excluded_product_category_id;
						break;
					default:
						break;
				}
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

    /**
	 * This prepares the $query_args for use in the connection query. This is where default $args are set, where dynamic
	 * $args from the $this->source get set, and where mapping the input $args to the actual $query_args occurs.
	 *
	 * @return mixed
	 */
	public function get_query_args() {
		return self::wc_query_args(
			parent::get_query_args(),
			$this->source,
			$this->args,
			$this->context,
			$this->info
		);
    }
}
