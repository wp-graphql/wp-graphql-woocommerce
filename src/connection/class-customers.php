<?php
/**
 * Connection type - Customers
 *
 * Registers connections to Customers
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Customers
 */
class Customers {
	/**
	 * Registers the various connections from other Types to Customer
	 */
	public static function register_connections() {
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'RootQuery',
					'toType'        => 'Customer',
					'fromFieldName' => 'customers',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'Customer',
					'fromFieldName' => 'usedBy',
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
	public static function get_connection_config( $args ) {
		$defaults = array(
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_customer( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_customer_connection( $source, $args, $context, $info );
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
			'search'    => array(
				'type'        => 'String',
				'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
			),
			'exclude'   => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Ensure result set excludes specific IDs.', 'wp-graphql-woocommerce' ),
			),
			'include'   => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to specific ids.', 'wp-graphql-woocommerce' ),
			),
			'email'     => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to resources with a specific email.', 'wp-graphql-woocommerce' ),
			),
			'role'      => array(
				'type'        => 'UserRoleEnum',
				'description' => __( 'Limit result set to resources with a specific role.', 'wp-graphql-woocommerce' ),
			),
			'roleIn'    => array(
				'type'        => array( 'list_of' => 'UserRoleEnum' ),
				'description' => __( 'Limit result set to resources with a specific group of roles.', 'wp-graphql-woocommerce' ),
			),
			'roleNotIn' => array(
				'type'        => array( 'list_of' => 'UserRoleEnum' ),
				'description' => __( 'Limit result set to resources not within a specific group of roles.', 'wp-graphql-woocommerce' ),
			),
			'orderby'   => array(
				'type'        => 'CustomerConnectionOrderbyEnum',
				'description' => __( 'Order results by a specific field.', 'wp-graphql-woocommerce' ),
			),
			'order'     => array(
				'type'        => 'OrderEnum',
				'description' => __( 'Order of results.', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
