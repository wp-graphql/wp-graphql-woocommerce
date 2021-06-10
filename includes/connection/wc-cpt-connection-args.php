<?php
/**
 * Define common connection arguments for CPT connections.
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.2.2
 */

namespace WPGraphQL\WooCommerce\Connection;

/**
 * Returns argument definitions for argument common on CPT connections.
 *
 * @return array
 */
function get_wc_cpt_connection_args(): array {
	return array(
		'search'      => array(
			'type'        => 'String',
			'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
		),
		'exclude'     => array(
			'type'        => array( 'list_of' => 'Int' ),
			'description' => __( 'Ensure result set excludes specific IDs.', 'wp-graphql-woocommerce' ),
		),
		'include'     => array(
			'type'        => array( 'list_of' => 'Int' ),
			'description' => __( 'Limit result set to specific ids.', 'wp-graphql-woocommerce' ),
		),
		'orderby'     => array(
			'type'        => array( 'list_of' => 'PostTypeOrderbyInput' ),
			'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
		),
		'dateQuery'   => array(
			'type'        => 'DateQueryInput',
			'description' => __( 'Filter the connection based on dates.', 'wp-graphql-woocommerce' ),
		),
		'parent'      => array(
			'type'        => 'Int',
			'description' => __( 'Use ID to return only children. Use 0 to return only top-level items.', 'wp-graphql-woocommerce' ),
		),
		'parentIn'    => array(
			'type'        => array( 'list_of' => 'Int' ),
			'description' => __( 'Specify objects whose parent is in an array.', 'wp-graphql-woocommerce' ),
		),
		'parentNotIn' => array(
			'type'        => array( 'list_of' => 'Int' ),
			'description' => __( 'Specify objects whose parent is not in an array.', 'wp-graphql-woocommerce' ),
		),
	);
}

/**
 * Sanitizes common post-type connection query input.
 *
 * @param array $input          Input to be sanitize.
 * @param array $ordering_meta  Meta types used for ordering results.
 *
 * @return array
 */
function map_shared_input_fields_to_wp_query( array $input, $ordering_meta = array() ) {
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
			} elseif ( in_array( $orderby_input['field'], $ordering_meta, true ) ) {
				$args['orderby']['meta_value_num'] = $orderby_input['order'];
				$args['meta_key']                  = esc_sql( $orderby_input['field'] ); // WPCS: slow query ok.

				// Handle post object fields.
			} elseif ( ! empty( $orderby_input['field'] ) ) {
				$args['orderby'][ esc_sql( $orderby_input['field'] ) ] = esc_sql( $orderby_input['order'] );
			}
		}
	}

	if ( ! empty( $input['dateQuery'] ) ) {
		$args['date_query'] = $input['dateQuery'];
	}

	return $args;
}
