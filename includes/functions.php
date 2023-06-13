<?php
/**
 * Misc functions.
 *
 * @package WPGraphQL\WooCommerce
 * @since 0.3.1
 * @deprecated v0.10.2
 *
 * Will be removed in v0.11.0
 */

namespace WPGraphQL\WooCommerce\Functions;

/**
 * Initializes minor integrations with other WordPress plugins.
 *
 * @return void
 */
function setup_minor_integrations() {
	add_filter(
		'graphql_swp_result_possible_types',
		'WPGraphQL\WooCommerce\Functions\woographql_swp_result_possible_types',
		10,
		1
	);
}

/**
 * QL Search integration - Adds to product types to the SWPResult possible types
 *
 * @param array $type_names SWPResults possible types.
 *
 * @return array
 */
function woographql_swp_result_possible_types( array $type_names ) {
	if ( in_array( 'Product', $type_names, true ) ) {
		$type_names = array_merge(
			array_filter(
				$type_names,
				function( $type_name ) {
					return 'Product' !== $type_name;
				}
			),
			[
				'SimpleProduct',
				'VariableProduct',
				'GroupProduct',
				'ExternalProduct',
			]
		);
	}

	return $type_names;
}
