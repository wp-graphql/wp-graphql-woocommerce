<?php
/**
 * DataLoader - WC_Crud_Loader
 *
 * Loads Models for WooCommerce CRUD objects
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Loader
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Loader;

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Data\Loader\AbstractDataLoader;
use WPGraphQL\Extensions\WooCommerce\Model\Coupon;
use WPGraphQL\Extensions\WooCommerce\Model\Product;
use WPGraphQL\Extensions\WooCommerce\Model\Product_Variation;
use WPGraphQL\Extensions\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Refund;

/**
 * Class WC_Crud_Loader
 */
class WC_Crud_Loader extends AbstractDataLoader {
	/**
	 * Stores loaded CRUD objects.
	 *
	 * @var array
	 */
	protected $loaded_objects;

	/**
	 * Returns CRUD object for provided IDs
	 *
	 * @param array $keys - array of IDs.
	 *
	 * @return array
	 * @throws UserError - throws if no corresponding Data store is found ID is not found.
	 */
	public function loadKeys( array $keys ) {
		if ( empty( $keys ) ) {
			return $keys;
		}

		$wc_post_types = array(
			'product',
			'product_variation',
			'shop_coupon',
			'shop_order',
			'shop_order_refund',
		);

		/**
		 * Prepare the args for the query. We're provided a specific
		 * set of IDs, so we want to query as efficiently as possible with
		 * as little overhead as possible. We don't want to return post counts,
		 * we don't want to include sticky posts, and we want to limit the query
		 * to the count of the keys provided. The query must also return results
		 * in the same order the keys were provided in.
		 */
		$args = array(
			'post_type'           => $wc_post_types,
			'post_status'         => 'any',
			'posts_per_page'      => count( $keys ),
			'post__in'            => $keys,
			'orderby'             => 'post__in',
			'no_found_rows'       => true,
			'split_the_query'     => false,
			'ignore_sticky_posts' => true,
			'fields'              => 'id=>type',
		);

		/**
		 * Ensure that WP_Query doesn't first ask for IDs since we already have them.
		 */
		add_filter(
			'split_the_query',
			function ( $split, \WP_Query $query ) {
				if ( false === $query->get( 'split_the_query' ) ) {
					return false;
				}
				return $split;
			},
			10,
			2
		);
		new \WP_Query( $args );
		/**
		 * Loop over the posts and return an array of all_posts,
		 * where the key is the ID and the value is the Post passed through
		 * the model layer.
		 */
		foreach ( $keys as $key ) {
			/**
			 * The query above has added our objects to the cache
			 * so now we can pluck them from the cache to return here
			 * and if they don't exist or aren't a valid post-type we can throw an error, otherwise
			 * we can proceed to resolve the object via the Model layer.
			 */
			$post_type = get_post_type( (int) $key );
			if ( ! $post_type ) {
				/* translators: invalid id error message */
				throw new UserError( sprintf( __( 'No item was found with ID %s', 'wp-graphql-woocommerce' ), $key ) );
			}

			if ( ! in_array( $post_type, $wc_post_types, false ) ) {
				/* translators: invalid post-type error message */
				throw new UserError( sprintf( __( '%s is not a valid WooCommerce post-type', 'wp-graphql-woocommerce' ), $post_type ) );
			}

			/**
			 * Return the instance through the Model to ensure we only
			 * return fields the consumer has access to.
			 */
			$this->loaded_objects[ $key ] = new Deferred(
				function() use ( $post_type, $key ) {
					switch ( $post_type ) {
						case 'product':
							return new Product( $key );
						case 'product_variation':
							return new Product_Variation( $key );
						case 'shop_coupon':
							return new Coupon( $key );
						case 'shop_order':
							return new Order( $key );
						case 'shop_order_refund':
							return new Refund( $key );
						default:
							$model = apply_filters( 'wc_crud_loader_model', null, $post_type );
							if ( ! empty( $model ) ) {
								return $model( $key );
							}
							return null;
					}
				}
			);
		}
		return ! empty( $this->loaded_objects ) ? $this->loaded_objects : array();
	}
}
