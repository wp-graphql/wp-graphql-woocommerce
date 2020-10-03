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

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use WPGraphQL\Data\Loader\AbstractDataLoader;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Model\Coupon;
use WPGraphQL\WooCommerce\Model\Product;
use WPGraphQL\WooCommerce\Model\Product_Variation;
use WPGraphQL\WooCommerce\Model\Order;
use WPGraphQL\WooCommerce\Model\Refund;

/**
 * Class WC_CPT_Loader
 */
class WC_CPT_Loader extends AbstractDataLoader {
	/**
	 * Stores loaded CPTs.
	 *
	 * @var array
	 */
	protected $loaded_objects;

	/**
	 * Returns the Model for a given post-type and ID.
	 *
	 * @param string  $post_type  WordPress post-type.
	 * @param int     $id         Post ID.
	 * @param boolean $fatal      Throw if no model found.
	 *
	 * @return mixed
	 * @throws UserError - throws if no corresponding Model is registered to the post-type.
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
				return new Order( $id );
			case 'shop_order_refund':
				return new Refund( $id );
			default:
				$model = apply_filters( 'graphql_woocommerce_cpt_loader_model', null, $post_type );
				if ( ! empty( $model ) ) {
					return new $model( $id );
				}

				// Bail if not fatal.
				if ( ! $fatal ) {
					return null;
				}

				/* translators: no model assigned error message */
				throw new UserError( sprintf( __( 'No Model is register to the custom post-type "%s"', 'wp-graphql-woocommerce' ), $post_type ) );
		}
	}

	/**
	 * Given array of keys, loads and returns a map consisting of keys from `keys` array and loaded
	 * posts as the values
	 *
	 * @param array $keys - array of IDs.
	 *
	 * @return array
	 * @throws UserError - throws if no corresponding Data store exists with the ID.
	 */
	public function loadKeys( array $keys ) {
		if ( empty( $keys ) ) {
			return $keys;
		}

		$wc_post_types = \WP_GraphQL_WooCommerce::get_post_types();
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
			}

			if ( ! in_array( $post_type, $wc_post_types, true ) ) {
				/* translators: invalid post-type error message */
				throw new UserError( sprintf( __( '%s is not a valid WooCommerce post-type', 'wp-graphql-woocommerce' ), $post_type ) );
			}

			/**
			 * If there's a customer connected to the order, we need to resolve the
			 * customer
			 */
			$context     = $this->context;
			$customer_id = null;
			$parent_id   = null;

			// Resolve post author for future capability checks.
			switch ( $post_type ) {
				case 'shop_order':
					$customer_id = get_post_meta( $key, '_customer_user', true );
					if ( ! empty( $customer_id ) ) {
						$this->context->getLoader( 'wc_customer' )->buffer( [ $customer_id ] );
					}
					break;
				case 'product_variation':
				case 'shop_refund':
					$parent_id = get_post_field( 'post_parent', $key );
					$this->buffer( [ $parent_id ] );
					break;
			}

			/**
			 * This is a deferred function that allows us to do batch loading
			 * of dependant resources. When the Model Layer attempts to determine
			 * access control of a Post, it needs to know the owner of it, and
			 * if it's a revision, it needs the Parent.
			 *
			 * This deferred function allows for the objects to be loaded all at once
			 * instead of loading once per entity, thus reducing the n+1 problem.
			 */
			$load_dependencies = new Deferred(
				function() use ( $key, $post_type, $customer_id, $parent_id, $context ) {
					if ( ! empty( $customer_id ) ) {
						$context->getLoader( 'wc_customer' )->load( $customer_id );
					}
					if ( ! empty( $parent_id ) ) {
						$this->load( $parent_id );
					}

					/**
					 * Run an action when the dependencies are being loaded for
					 * Post Objects
					 */
					do_action( 'woographql_cpt_loader_load_dependencies', $this, $key, $post_type );

					return;
				}
			);

			/**
			 * Once dependencies are loaded, return the Post Object
			 */
			$loaded_posts[ $key ] = $load_dependencies->then(
				function() use ( $post_type, $key ) {
					return self::resolve_model( $post_type, $key );
				}
			);
		}

		return ! empty( $loaded_posts ) ? $loaded_posts : [];
	}

	/**
	 * Callback for inject the PostObject dataloader with WC_Post models.
	 *
	 * @param null $model
	 * @param mixed $entry
	 * @param mixed $key
	 * @return void
	 */
	public static function inject_post_loader_models( $model, $entry, $key ) {
		if ( is_a( $entry, \WP_Post::class ) ) {
			$model = self::resolve_model( $entry->post_type, $key, false );
		}

		return $model;
	}
}
