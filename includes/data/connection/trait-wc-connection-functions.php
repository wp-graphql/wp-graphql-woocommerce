<?php
/**
 * Defines reusable functions for all connection.
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.3.3
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Trait WC_Connection_Functions
 */
trait WC_Connection_Functions {
	/**
	 * Determine whether or not the the offset is valid, i.e the post corresponding to the offset exists.
	 * Offset is equivalent to post_id. So this function is equivalent
	 * to checking if the post with the given ID exists.
	 *
	 * @param integer $offset  Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_post_offset( $offset ) {
		global $wpdb;

		if ( ! empty( wp_cache_get( $offset, 'posts' ) ) ) {
			return true;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS (SELECT 1 FROM $wpdb->posts WHERE ID = %d)", $offset ) );
	}

	/**
	 * Determine whether or not the the offset is valid, i.e the cart item corresponding to the offset exists.
	 * Offset is equivalent to a cart item key. So this function is equivalent
	 * to checking if the cart item with the given key exists.
	 *
	 * @param string $offset  Cart item key.
	 *
	 * @return bool
	 */
	public function is_valid_cart_item_offset( $offset ) {
		return ! empty( $this->source->get_cart_item( $offset ) );
	}

	/**
	 * Determine whether or not the the offset is valid, i.e the user corresponding to the offset exists.
	 * Offset is equivalent to user_id. So this function is equivalent
	 * to checking if the user with the given ID exists.
	 *
	 * @param integer $offset  User ID.
	 *
	 * @return bool
	 */
	public function is_valid_user_offset( $offset ) {
		global $wpdb;

		if ( ! empty( wp_cache_get( $offset, 'users' ) ) ) {
			return true;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS (SELECT 1 FROM $wpdb->users WHERE ID = %d)", $offset ) );
	}

	/**
	 * Sanitizes common post-type connection query input.
	 *
	 * @param array $input  Input to be sanitize.
	 *
	 * @return array
	 */
	public function sanitize_common_inputs( array $input ) {
		$args = array();
		if ( ! empty( $input['include'] ) ) {
			$args['post__in'] = $input['include'];
		}

		if ( ! empty( $input['exclude'] ) ) {
			$args['post__not_in'] = $input['exclude'];
		}

		if ( ! empty( $input['parent'] ) ) {
			$args['post_parent'] = $input['parent'];
		}

		if ( ! empty( $input['parentIn'] ) ) {
			if ( ! isset( $args['post_parent__in'] ) ) {
				$args['post_parent__in'] = array();
			}
			$args['post_parent__in'] = array_merge( $args['post_parent__in'], $input['parentIn'] );
		}

		if ( ! empty( $input['parentNotIn'] ) ) {
			$args['post_parent__not_in'] = $input['parentNotIn'];
		}

		if ( ! empty( $input['search'] ) ) {
			$args['s'] = $input['search'];
		}

		/**
		 * Map the orderby inputArgs to the WP_Query
		 */
		if ( ! empty( $input['orderby'] ) && is_array( $input['orderby'] ) ) {
			$args['orderby'] = array();
			foreach ( $input['orderby'] as $orderby_input ) {
				/**
				 * These orderby options should not include the order parameter.
				 */
				if ( in_array(
					$orderby_input['field'],
					array( 'post__in', 'post_name__in', 'post_parent__in' ),
					true
				) ) {
					$args['orderby'] = esc_sql( $orderby_input['field'] );

					// Handle meta fields.
				} elseif ( in_array( $orderby_input['field'], $this->ordering_meta(), true ) ) {
					$args['orderby']  = array( 'meta_value_num' => $orderby_input['order'] );
					$args['meta_key'] = esc_sql( $orderby_input['field'] ); // WPCS: slow query ok.

					// Handle post object fields.
				} elseif ( ! empty( $orderby_input['field'] ) ) {
					$args['orderby'] = array(
						esc_sql( $orderby_input['field'] ) => esc_sql( $orderby_input['order'] ),
					);
				}
			}
		}

		if ( ! empty( $input['dateQuery'] ) ) {
			$args['date_query'] = $input['dateQuery'];
		}

		return $args;
	}

	/**
	 * Return WooCommerce CPT models by ID.
	 * 
	 * @param int $id ID.
	 * 
	 * @return mixed|null
	 */
	public function get_cpt_model_by_id( $id ) {
		return $this->loader->resolve_model( get_post_type( $id ), $id );
	}

	/**
	 * Given an offset and prefix, a cursor is returned
	 *
	 * @param string         $prefix Salt.
	 * @param string|integer $offset Connection offset.
	 *
	 * @return string
	 */
	protected function offset_to_cursor( $prefix, $offset ) {
		return base64_encode( "{$prefix}:{$offset}" );
	}

	/**
	 * Given a valid cursor and prefix, the offset is returned
	 *
	 * @param string $prefix Salt.
	 * @param string $cursor Cursor.
	 *
	 * @return string|integer
	 */
	protected function cursor_to_offset( $prefix, $cursor ) {
		return substr( base64_decode( $cursor ), strlen( $prefix . ':' ) );
	}
}
