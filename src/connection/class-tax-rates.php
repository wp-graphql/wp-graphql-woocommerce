<?php
/**
 * Connection - Tax_Rates
 *
 * Registers connections to TaxRate
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Tax_Rates
 */
class Tax_Rates {
	/**
	 * Registers the various connections from other Types to TaxRate
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
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
			'fromType'       => 'RootQuery',
			'toType'         => 'TaxRate',
			'fromFieldName'  => 'taxRates',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_tax_rate( $id );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_tax_rate_connection( $source, $args, $context, $info );
			},
		);
		return array_merge( $defaults, $args );
	}

	/**
	 * Returns array of where args
	 *
	 * @return array
	 */
	public static function get_connection_args() {
		return array(
			'class'   => array(
				'type'        => 'String',
				'description' => __( 'Sort by tax class', 'wp-graphql-woocommerce' ),
			),
			'orderby' => array(
				'type'        => array( 'list_of' => 'TaxRateConnectionOrderbyInput' ),
				'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
