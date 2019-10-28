<?php
/**
 * Defines reusable function for sanitizing user input for post-type connections.
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.2.2
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

/**
 * Trait Common_CPT_Input_Sanitize_Functions
 */
trait Common_CPT_Input_Sanitize_Functions {
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
}
