<?php
/**
 * Connection type - VariationAttributes
 *
 * Registers connections to VariationAttribute
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.4
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Product_Attributes
 */
class Variation_Attributes {
	/**
	 * Registers the various connections from other Types to VariationAttribute
	 */
	public static function register_connections() {
		// From ProductVariation.
		register_graphql_connection( self::get_connection_config() );
		// From Product.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'defaultAttributes',
				)
			)
		);
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
			'fromType'       => 'ProductVariation',
			'toType'         => 'VariationAttribute',
			'fromFieldName'  => 'attributes',
			'connectionArgs' => array(),
			'resolve'        => function ( $root, $args, $context, $info ) {
				return Factory::resolve_variation_attribute_connection( $root, $args, $context, $info );
			},
		);

		return array_merge( $defaults, $args );
	}
}
