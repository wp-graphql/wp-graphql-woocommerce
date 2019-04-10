<?php
/**
 * ConnectionResolver Trait - WC_Connection_Resolver
 *
 * Defines shared functionality for WooCommerce connection resolvers
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

/**
 * Trait WC_Connection_Resolver
 */
trait WC_Connection_Resolver {
	/**
	 * Get order statuses without prefixes.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_order_statuses() {
		$order_statuses = array();
		foreach ( array_keys( wc_get_order_statuses() ) as $status ) {
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
		$args = array();
		if ( ! empty( $where_args['include'] ) ) {
			$args['post__in'] = $where_args['include'];
		}

		if ( ! empty( $where_args['exclude'] ) ) {

			$args['post__not_in'] = $where_args['exclude'];
		}

		if ( ! empty( $where_args['parent'] ) ) {
			$args['post_parent'] = $where_args['parent'];
		}

		if ( ! empty( $where_args['parentIn'] ) ) {
			if ( ! isset( $args['post_parent__in'] ) ) {
				$args['post_parent__in'] = array();
			}
			$args['post_parent__in'] = array_merge( $args['post_parent__in'], $where_args['parentIn'] );
		}

		if ( ! empty( $where_args['parentNotIn'] ) ) {
			$args['post_parent__not_in'] = $where_args['parentNotIn'];
		}

		if ( ! empty( $where_args['search'] ) ) {
			$args['s'] = $where_args['search'];
		}

		/**
		 * Map the orderby inputArgs to the WP_Query
		 */
		if ( ! empty( $where_args['orderby'] ) && is_array( $where_args['orderby'] ) ) {
			$args['orderby'] = [];
			foreach ( $where_args['orderby'] as $orderby_input ) {
				/**
				 * These orderby options should not include the order parameter.
				 */
				if ( in_array(
					$orderby_input['field'],
					array( 'post__in', 'post_name__in', 'post_parent__in' ),
					true
				) ) {
					$args['orderby'] = esc_sql( $orderby_input['field'] );
				} elseif ( ! empty( $orderby_input['field'] ) ) {
					$args['orderby'] = array(
						esc_sql( $orderby_input['field'] ) => esc_sql( $orderby_input['order'] ),
					);
				}
			}
		}

		/**
		 * Convert meta_value_num to seperate meta_value value field which our
		 * graphql_wp_term_query_cursor_pagination_support knowns how to handle
		 */
		if ( isset( $args['orderby'] ) && 'meta_value_num' === $args['orderby'] ) {
			$args['orderby'] = array(
				'meta_value' => empty( $args['order'] ) ? 'DESC' : $args['order'], // WPCS: slow query ok.
			);
			unset( $args['order'] );
			$args['meta_type'] = 'NUMERIC';
		}

		if ( ! empty( $where_args['dateQuery'] ) ) {
			$args['date_query'] = $where_args['dateQuery'];
		}

		return $args;
	}
}
