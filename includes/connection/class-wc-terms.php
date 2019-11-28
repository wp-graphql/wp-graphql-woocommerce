<?php
/**
 * Connection type - ProductTags
 *
 * Registers connections to ProductTags
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Connection;

use WPGraphQL\Connection\TermObjects;

/**
 * Class - WC_Terms
 */
class WC_Terms extends TermObjects {
	/**
	 * Registers the various connections from other Types to WooCommerce taxonomies
	 */
	public static function register_connections() {
		$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();
		$wc_post_types      = \WP_GraphQL_WooCommerce::get_post_types();
		/**
		 * Loop through the allowed_taxonomies to register appropriate connections
		 */
		if ( ! empty( $allowed_taxonomies && is_array( $allowed_taxonomies ) ) ) {
			foreach ( $allowed_taxonomies as $taxonomy ) {
				$tax_object = get_taxonomy( $taxonomy );

				/**
				 * Registers the connections between each allowed PostObjectType and it's TermObjects
				 */
				if ( ! empty( $wc_post_types ) && is_array( $wc_post_types ) ) {
					foreach ( $wc_post_types as $post_type ) {
						if ( in_array( $post_type, $tax_object->object_type, true ) ) {
							$post_type_object = get_post_type_object( $post_type );
							register_graphql_connection(
								self::get_connection_config(
									$tax_object,
									array(
										'fromType'      => $post_type_object->graphql_single_name,
										'toType'        => $tax_object->graphql_single_name,
										'fromFieldName' => $tax_object->graphql_plural_name,
									)
								)
							);
						}
					}
				}
			}
		}

		// From Coupons.
		register_graphql_connection(
			self::get_connection_config(
				get_taxonomy( 'product_cat' ),
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'productCategories',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				get_taxonomy( 'product_cat' ),
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'excludedProductCategories',
				)
			)
		);
	}
}
