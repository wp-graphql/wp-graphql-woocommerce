<?php
/**
 * ConnectionResolver - Product_Connection_Resolver
 *
 * Resolves connections to Products
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Model\Coupon;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;
use WPGraphQL\Extensions\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Product;
use WPGraphQL\Model\Term;

/**
 * Class Product_Connection_Resolver
 */
class Product_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Confirms the uses has the privileges to query Products
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates query arguments array
	 */
	public function get_query_args() {
		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		// Set the $query_args based on various defaults and primary input $args.
		$post_type_obj = get_post_type_object( 'product' );
		$query_args    = array(
			'post_type'      => 'product',
			'post_parent'    => 0,
			'post_status'    => current_user_can( $post_type_obj->cap->edit_posts ) ? 'any' : 'publish',
			'perm'           => 'readable',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

		// Determine where we're at in the Graph and adjust the query context appropriately.
		if ( true === is_object( $this->source ) ) {
			switch ( true ) {
				case is_a( $this->source, Coupon::class ):
					if ( 'excludedProducts' === $this->info->fieldName ) {
						$query_args['post__in'] = ! empty( $this->source->excluded_product_ids ) ? $this->source->excluded_product_ids : [ '0' ];
					} else {
						$query_args['post__in'] = ! empty( $this->source->product_ids ) ? $this->source->product_ids : [ '0' ];
					}
					break;

				case is_a( $this->source, Customer::class ):
					break;

				case is_a( $this->source, Order::class ):
					break;

				case is_a( $this->source, Product::class ):
					if ( 'upsell' === $this->info->fieldName ) {
						$query_args['post__in'] = $this->source->upsell_ids;
					} elseif ( 'crossSell' === $this->info->fieldName ) {
						$query_args['post__in'] = $this->source->cross_sell_ids;
					} elseif ( 'variations' === $this->info->fieldName ) {
						$query_args['post_parent'] = $this->source->ID;
						$query_args['post__in']    = $this->source->children_ids;
						$query_args['post_type']   = 'product_variation';
					}
					break;

				case is_a( $this->source, Term::class ):
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => $this->source->taxonomy,
							'terms'    => array( $this->source->term_id ),
							'field'    => 'term_id',
						),
					);
					break;
			}
		}

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return \WP_Query
	 */
	public function get_query() {
		return new \WP_Query( $this->get_query_args() );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		return ! empty( $this->query->posts ) ? $this->query->posts : [];
	}
}
