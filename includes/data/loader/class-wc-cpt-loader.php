<?php
/**
 * DataLoader - WC_CPT_Loader
 *
 * Loads Models for WooCommerce CPTs
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

use Automattic\WooCommerce\Utilities\OrderUtil;
use GraphQL\Error\UserError;
use WPGraphQL\Data\Loader\AbstractDataLoader;
use WPGraphQL\WooCommerce\Model\Coupon;
use WPGraphQL\WooCommerce\Model\Order;
use WPGraphQL\WooCommerce\Model\Product;
use WPGraphQL\WooCommerce\Model\Product_Variation;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class WC_CPT_Loader
 */
class WC_CPT_Loader extends AbstractDataLoader {
	/**
	 * Returns the Model for a given post-type and ID.
	 *
	 * @param string  $post_type  WordPress post-type.
	 * @param int     $id         Post ID.
	 * @param boolean $fatal      Throw if no model found.
	 *
	 * @throws \GraphQL\Error\UserError - throws if no corresponding Model is registered to the post-type.
	 * 
	 * @return \WPGraphQL\Model\Model|null
	 */
	public static function resolve_model( $post_type, $id, $fatal = true ) {
		switch ( $post_type ) {
			case 'product':
				return new Product( $id );
			case 'product_variation':
				return new Product_Variation( $id );
			case 'shop_coupon':
				return new Coupon( $id );
			case 'shop_order':
			case 'shop_order_refund':
			case 'shop_order_placehold':
				return new Order( $id );
			default:
				$model = apply_filters( 'graphql_woocommerce_cpt_loader_model', null, $post_type );
				if ( ! empty( $model ) ) {
					/**
					 * If a model is registered to the post-type, we can return an instance of that model
					 * with the post ID passed in.
					 * 
					 * @var \WPGraphQL\Model\Model
					 */
					return new $model( $id );
				}

				// Bail if not fatal.
				if ( ! $fatal ) {
					return null;
				}

				/* translators: no model assigned error message */
				throw new UserError( sprintf( __( 'No Model is register to the custom post-type "%s"', 'wp-graphql-woocommerce' ), $post_type ) );
		}//end switch
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \GraphQL\Error\UserError - throws if the post-type is not a valid WooCommerce post-type.
	 */
	public function loadKeys( array $keys ) {
		if ( empty( $keys ) ) {
			return $keys;
		}

		$wc_post_types = WP_GraphQL_WooCommerce::get_post_types();
		/**
		 * Prepare the args for the query. We're provided a specific
		 * set of IDs, so we want to query as efficiently as possible with
		 * as little overhead as possible. We don't want to return post counts,
		 * we don't want to include sticky posts, and we want to limit the query
		 * to the count of the keys provided. The query must also return results
		 * in the same order the keys were provided in.
		 */
		$args = [
			'post_type'           => $wc_post_types,
			'post_status'         => 'any',
			//phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'posts_per_page'      => count( $keys ),
			'post__in'            => $keys,
			'orderby'             => 'post__in',
			'no_found_rows'       => true,
			'split_the_query'     => false,
			'ignore_sticky_posts' => true,
		];

		/**
		 * Ensure that WP_Query doesn't first ask for IDs since we already have them.
		 */
		add_filter(
			'split_the_query',
			static function ( $split, \WP_Query $query ) {
				if ( false === $query->get( 'split_the_query' ) ) {
					return false;
				}
				return $split;
			},
			10,
			2
		);
		new \WP_Query( $args );

		$loaded_posts = [];

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
			$post_type = get_post_type( $key );
			if ( ! $post_type ) {
				$loaded_posts[ $key ] = null;
				continue;
			}

			if ( ! in_array( $post_type, $wc_post_types, true ) && ! OrderUtil::is_order( $key, wc_get_order_types() ) ) {
				/* translators: invalid post-type error message */
				throw new UserError( sprintf( __( '%s is not a valid WooCommerce post-type', 'wp-graphql-woocommerce' ), $post_type ) );
			}

			$post_object = get_post( (int) $key );
			if ( ! $post_object instanceof \WP_Post ) {
				$loaded_posts[ $key ] = null;
			} else {
				$loaded_posts[ $key ] = $post_object;
			}
		}//end foreach

		return $loaded_posts;
	}

	/**
	 * {@inheritDoc}
	 * 
	 * @return \WPGraphQL\Model\Model|null
	 */
	protected function get_model( $entry, $key ) {
		if ( ! $entry ) {
			return null;
		}

		/**
		 * If there's a customer connected to the order, we need to resolve the
		 * customer
		 */
		$context = $this->context;

		// Resolve post author for future capability checks.
		switch ( $entry->post_type ) {
			case 'shop_order':
				$customer_id = get_post_meta( $key, '_customer_user', true );
				if ( ! empty( $customer_id ) ) {
					$context->get_loader( 'wc_customer' )->load_deferred( $customer_id );
				}
				break;
			case 'product_variation':
			case 'shop_refund':
				$parent_id = $entry->post_parent;
				if ( ! empty( $entry->post_parent ) ) {
					$context->get_loader( 'wc_post' )->load_deferred( $entry->post_parent );
				}
				break;
		}

		return self::resolve_model( $entry->post_type, $key, false );
	}

	/**
	 * Callback for inject the PostObject dataloader with WC_Post models.
	 *
	 * @param null  $model  Possible model instance to be loader.
	 * @param mixed $entry  Data source.
	 * @param mixed $key    Data key/ID.
	 * @return \WPGraphQL\Model\Model|null
	 */
	public static function inject_post_loader_models( $model, $entry, $key ) {
		if ( is_a( $entry, \WP_Post::class ) ) {
			$model = self::resolve_model( $entry->post_type, $key, false );
		}

		return $model;
	}
}
