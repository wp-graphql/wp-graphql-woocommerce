<?php
/**
 * This file contains access functions for various class methods
 *
 * @package WPGraphQL\Extensions\WooCommerce
 * @since 0.0.1
 */

/**
 * Adds an ObjectType to the TypeRegistry and node resolvers to Relay node definitions.
 *
 * @param string $type_name The name of the Type to register.
 * @param array  $config    The Type config.
 */
function wc_register_graphql_object_type( $type_name, $config ) {
	$config['kind'] = 'object';
	if ( ! empty( $config['resolve_node'] ) ) {
		add_filter( 'graphql_resolve_node', $config['resolve_node'], 10, 4 );
		unset( $config['resolve_node'] );
	}
	if ( ! empty( $config['resolve_node_type'] ) ) {
		add_filter( 'graphql_resolve_node_type', $config['resolve_node_type'], 10, 2 );
		unset( $config['resolve_node_type'] );
	}
	register_graphql_type( $type_name, $config );
}

/**
 * Checks if source string starts with the target string
 *
 * @param string $haystack - Source string.
 * @param string $needle - Target string.
 *
 * @return bool
 */
function wc_graphql_starts_with( $haystack, $needle ) {
	$length = strlen( $needle );
	return ( substr( $haystack, 0, $length ) === $needle );
}

/**
 * Checks if source string ends with the target string
 *
 * @param string $haystack - Source string.
 * @param string $needle - Target string.
 *
 * @return bool
 */
function wc_graphql_ends_with( $haystack, $needle ) {
	$length = strlen( $needle );
	if ( 0 === $length ) {
		return true;
	}

	return ( substr( $haystack, -$length ) === $needle );
}

/**
 * Returns formatted array of tax statement objects.
 *
 * @param array $raw_taxes - array of raw taxes object from WC_Order_Item crud objects.
 *
 * @return array
 */
function wc_graphql_map_tax_statements( $raw_taxes ) {
	$taxes = array();
	foreach ( $raw_taxes as $field => $values ) {
		foreach ( $values as $id => $amount ) {
			if ( empty( $taxes[ $id ] ) ) {
				$taxes[ $id ] = array();
			}
			$taxes[ $id ]['ID']     = $id;
			$taxes[ $id ][ $field ] = $amount;
		}
	}

	return array_values( $taxes );
}

/**
 * Get order statuses without prefixes.
 *
 * @return array
 */
function wc_graphql_get_order_statuses() {
	$order_statuses = array();
	foreach ( array_keys( wc_get_order_statuses() ) as $status ) {
		$order_statuses[] = str_replace( 'wc-', '', $status );
	}
	return $order_statuses;
}
