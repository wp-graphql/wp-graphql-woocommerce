<?php
/**
 * Defines generic functions for to be used in connections that process creates using the CPT Loader.
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.5.0
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Trait WC_CPT_Loader_Common
 */
trait WC_CPT_Loader_Common {
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
					$args['orderby']['meta_value_num'] = $orderby_input['order'];
					$args['meta_key'] = esc_sql( $orderby_input['field'] ); // WPCS: slow query ok.

					// Handle post object fields.
				} elseif ( ! empty( $orderby_input['field'] ) ) {
					$args['orderby'][esc_sql( $orderby_input['field'] )] = esc_sql( $orderby_input['order'] );
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
}
