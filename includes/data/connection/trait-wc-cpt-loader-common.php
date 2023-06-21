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
 *
 * @property \WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader $loader
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
		$args = [];
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
			$args['post_parent__in'] = $input['parentIn'];
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
			$args['orderby'] = [];
			foreach ( $input['orderby'] as $orderby_input ) {
				/**
				 * Stores orderby field
				 *
				 * @var null|string $orderby_field
				 */
				$orderby_field = isset( $orderby_input['field'] ) ? (string) $orderby_input['field'] : null;

				if ( null === $orderby_field ) {
					continue;
				}

				$default_order = isset( $this->args['last'] ) ? 'ASC' : 'DESC';
				/**
				 * Stores orderby direction
				 *
				 * @var string $orderby_order
				 */
				$orderby_order = isset( $orderby_input['order'] ) ? $orderby_input['order'] : $default_order;

				/**
				 * These orderby options should not include the order parameter.
				 */
				$post_fields = [ 'post__in', 'post_name__in', 'post_parent__in' ];
				if ( in_array( $orderby_field, $post_fields, true ) ) {
					$args['orderby'][ $orderby_field ] = $orderby_order;

					// Handle meta fields.
				} elseif ( in_array( $orderby_field, $this->ordering_meta(), true ) ) {
					$args['orderby']['meta_value_num'] = $orderby_order;
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$args['meta_key'] = $orderby_field;
				} else {
					$args['orderby'][ $orderby_field ] = $orderby_order;
				}
			}//end foreach
		}//end if

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
		$post_type = get_post_type( $id );

		if ( ! $post_type ) {
			return null;
		}

		return $this->loader->resolve_model( $post_type, $id );
	}
}
