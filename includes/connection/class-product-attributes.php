<?php
/**
 * Connection type - ProductAttributes
 *
 * Registers connections to ProductAttribute
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Product_Attributes
 */
class Product_Attributes {
	/**
	 * Registers the various connections from other Types to ProductAttribute
	 */
	public static function register_connections() {
		// From product types.
		$product_types = array_values( \WP_GraphQL_WooCommerce::get_enabled_product_types() );
		foreach ( $product_types as $product_type ) {
			register_graphql_connection(
				self::get_connection_config(
					array( 'fromType' => $product_type )
				)
			);
		}
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args - Connection configuration.
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = array() ) {
		$defaults = array(
			'fromType'       => 'SimpleProduct',
			'toType'         => 'ProductAttribute',
			'fromFieldName'  => 'attributes',
			'connectionArgs' => array(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return Factory::resolve_product_attribute_connection( $root, $args, $context, $info );
			},
		);

		return array_merge( $defaults, $args );
	}
}
