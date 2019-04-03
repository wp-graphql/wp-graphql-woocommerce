<?php
/**
 * ConnectionResolver - Refund_Connection_Resolver
 *
 * Resolves connections to Refunds
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extension\WooCommerce\Model\Customer;
use WPGraphQL\Extension\WooCommerce\Model\Order;

/**
 * Class Refund_Connection_Resolver
 */
class Refund_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Confirms the uses has the privileges to query Refunds
	 *
	 * @return bool
	 */
	public function should_execute() {
		$post_type_obj = get_post_type_object( 'shop_order_refund' );
		switch ( true ) {
			case current_user_can( $post_type_obj->cap->edit_posts ):
			case is_a( $this->source, Order::class ) && 'refunds' === $this->info->fieldName:
			case is_a( $this->source, Customer::class ) && 'refunds' === $this->info->fieldName:
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
			'post_type'      => 'shop_order_refund',
			'post_status'    => 'any',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
		);

		switch ( true ) {
			case is_a( $this->source, Order::class ):
				if ( 'refunds' === $this->info->fieldName ) {
					$query_args['post_parent'] = $this->source->ID;
				}
				break;
			case is_a( $this->source, Customer::class ):
				if ( 'refunds' === $this->info->fieldName ) {
					$query_args['meta_key']   = '_customer_user';
					$query_args['meta_value'] = $this->source->ID;
				}
				break;
			default:
				break;
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
