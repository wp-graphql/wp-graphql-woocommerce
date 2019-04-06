<?php
/**
 * This file contains access functions for various class methods
 *
 * @package     WPGraphQL\Extensions\WooCommerce
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
