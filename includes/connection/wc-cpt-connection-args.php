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
	return [
		'search'      => [
			'type'        => 'String',
			'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
		],
		'exclude'     => [
			'type'        => [ 'list_of' => 'Int' ],
			'description' => __( 'Ensure result set excludes specific IDs.', 'wp-graphql-woocommerce' ),
		],
		'include'     => [
			'type'        => [ 'list_of' => 'Int' ],
			'description' => __( 'Limit result set to specific ids.', 'wp-graphql-woocommerce' ),
		],
		'orderby'     => [
			'type'        => [ 'list_of' => 'PostTypeOrderbyInput' ],
			'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
		],
		'dateQuery'   => [
			'type'        => 'DateQueryInput',
			'description' => __( 'Filter the connection based on dates.', 'wp-graphql-woocommerce' ),
		],
		'parent'      => [
			'type'        => 'Int',
			'description' => __( 'Use ID to return only children. Use 0 to return only top-level items.', 'wp-graphql-woocommerce' ),
		],
		'parentIn'    => [
			'type'        => [ 'list_of' => 'Int' ],
			'description' => __( 'Specify objects whose parent is in an array.', 'wp-graphql-woocommerce' ),
		],
		'parentNotIn' => [
			'type'        => [ 'list_of' => 'Int' ],
			'description' => __( 'Specify objects whose parent is not in an array.', 'wp-graphql-woocommerce' ),
		],
	];
}

/**
 * Sanitizes common post-type connection query input.
 *
 * @param array $input          Input to be sanitize.
 * @param array $ordering_meta  Meta types used for ordering results.
 *
 * @return array
 */
function map_shared_input_fields_to_wp_query( array $input, $ordering_meta = [] ) {
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

			$default_order = 'ASC';
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
			} elseif ( in_array( $orderby_field, $ordering_meta, true ) ) {
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
