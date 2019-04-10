<?php
/**
 * ConnectionResolver - Order_Connection_Resolver
 *
 * Resolves connections to Orders
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Extension\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;

/**
 * Class Order_Connection_Resolver
 */
class Order_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Confirms the uses has the privileges to query Orders
	 *
	 * @return bool
	 */
	public function should_execute() {
		$post_type_obj = get_post_type_object( 'shop_order' );
		switch ( true ) {
			case current_user_can( $post_type_obj->cap->edit_posts ):
			case is_a( $this->source, Customer::class ) && 'orders' === $this->info->fieldName:
				return true;
			default:
				return false;
		}
	}

	/**
	 * Creates query arguments array
	 */
	public function get_query_args() {
		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		// Set the $query_args based on various defaults and primary input $args.
		$query_args = array(
			'post_type'      => 'shop_order',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = [];
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}

		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		if ( empty( $query_args['post_status'] ) ) {
			$query_args['post_status'] = 'any';
		}

		if ( true === is_object( $this->source ) ) {
			switch ( true ) {
				case is_a( $this->source, Customer::class ):
					if ( 'orders' === $this->info->fieldName ) {
						if ( ! empty( $args['meta_query'] ) ) {
							$args['meta_query'] = array(); // WPCS: slow query ok.
						}
						$args['meta_query'][] = array(
							'key'   => '_customer_user',
							'value' => $this->source->ID,
							'type'  => 'NUMERIC',
						);
					}
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

	/**
	 * Get order statuses without prefixes.
	 *
	 * @return array
	 */
	protected function get_order_statuses() {
		$order_statuses = array();
		foreach ( array_keys( \wc_get_order_statuses() ) as $status ) {
			$order_statuses[] = str_replace( 'wc-', '', $status );
		}
		return $order_statuses;
	}

	/**
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to WP_Query
	 * friendly keys. There's probably a cleaner/more dynamic way to approach this, but
	 * this was quick. I'd be down to explore more dynamic ways to map this, but for
	 * now this gets the job done.
	 *
	 * @param array $where_args - arguments being used to filter query.
	 *
	 * @return array
	 */
	public function sanitize_input_fields( array $where_args ) {
		global $wpdb;
		$args = array();

		if ( ! empty( $where_args['statuses'] ) ) {
			$args['post_status'] = array();
			foreach ( $where_args['statuses'] as $status ) {
				if ( in_array( $status, $this->get_order_statuses(), true ) ) {
					$args['post_status'][] = 'wc-' . $status;
				} elseif ( 'any' === $status ) {
					// Set status to "any" and short-circuit out.
					$args['post_status'] = 'any';
					break;
				} else {
					$args['post_status'][] = $status;
				}
			}
		}

		if ( ! empty( $where_args['customerId'] ) ) {
			if ( ! empty( $args['meta_query'] ) ) {
				$args['meta_query'] = array(); // WPCS: slow query ok.
			}
			$args['meta_query'][] = array(
				'key'   => '_customer_user',
				'value' => $where_args['customerId'],
				'type'  => 'NUMERIC',
			);
		}

		// Search by product.
		if ( ! empty( $where_args['productId'] ) ) {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT order_id
					FROM {$wpdb->prefix}woocommerce_order_items
					WHERE order_item_id IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND meta_value = %d )
					AND order_item_type = 'line_item'",
					absint( $where_args['productId'] )
				)
			);

			// Force WP_Query return empty if don't found any order.
			$args['post__in'] = ! empty( $order_ids ) ? $order_ids : array( 0 );
		}

		// Search.
		if ( ! empty( $args['s'] ) ) {
			$order_ids = wc_order_search( $args['s'] );
			if ( ! empty( $order_ids ) ) {
				unset( $args['s'] );
				$args['post__in'] = array_merge( $order_ids, array( 0 ) );
			}
		}

		return $args;
	}
}
