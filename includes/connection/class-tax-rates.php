<?php
/**
 * Connection - Tax_Rates
 *
 * Registers connections to TaxRate
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

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
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		return array_merge(
			array(
				'fromType'       => 'RootQuery',
				'toType'         => 'TaxRate',
				'fromFieldName'  => 'taxRates',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					return Factory::resolve_tax_rate_connection( $source, $args, $context, $info );
				},
			),
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		return array(
			'class'      => array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'Sort by tax class.', 'wp-graphql-woocommerce' ),
			),
			'postCode'   => array(
				'type'        => 'String',
				'description' => __( 'Filter results by a post code.', 'wp-graphql-woocommerce' ),
			),
			'postCodeIn' => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Filter results by a group of post codes.', 'wp-graphql-woocommerce' ),
			),
			'orderby'    => array(
				'type'        => array( 'list_of' => 'TaxRateConnectionOrderbyInput' ),
				'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
