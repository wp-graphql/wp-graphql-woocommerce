<?php
/**
 * Defines WooGraphQL's substitutions settings.
 *
 * @package WPGraphQL\WooCommerce\Admin
 */

namespace WPGraphQL\WooCommerce\Admin;

use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;

/**
 * General class
 */
class Substitutions extends Section {

	/**
	 * Return option list of valid GraphQL types for products.
	 *
	 * @return array
	 */
	public static function get_dropdown_list() {
		$graphql_types = [];
		foreach ( WooGraphQL::get_enabled_product_types() as $type ) {
			$graphql_types[ $type ] = $type;
		}
		return $graphql_types;
	}

	/**
	 * Returns General settings fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		$type_labels      = wc_get_product_types();
		$registered_types = array_keys( WooGraphQL::get_enabled_product_types() );
		$all_types        = array_keys( $type_labels );

		$unregistered_types = array_diff( $registered_types, $all_types );
		$fields             = [];
		foreach ( $unregistered_types as $product_type ) {
			$fields[] = [
				'name'    => "{$product_type}_substitution_type",
				'label'   => sprintf(
					/* translators: product type */
					__( 'Substitution type for %s', 'wp-graphql-woocommerce' ),
					$type_labels[ $product_type ]
				),
				'desc'    => sprintf(
					/* translators: product type */
					__( 'Set a replacement GraphQL type for %s because it\'s unregistered', 'wp-graphql-woocommerce' ),
					$type_labels[ $product_type ]
				),
				'type'    => 'select',
				'options' => self::get_dropdown_list(),
			];
		}

		return $fields;
	}
}
