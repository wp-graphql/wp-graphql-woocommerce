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

		// From Products.
		register_graphql_connection(
			self::get_connection_config(
				get_taxonomy( 'product_cat' ),
				array(
					'fromType'      => 'Product',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'categories',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				get_taxonomy( 'product_tag' ),
				array(
					'fromType'      => 'Product',
					'toType'        => 'ProductTag',
					'fromFieldName' => 'tags',
				)
			)
		);

		// From Product child types.
		$product_types = array_values( \WP_GraphQL_WooCommerce::get_enabled_product_types() );
		foreach ( $product_types as $product_type ) {
			register_graphql_connection(
				self::get_connection_config(
					get_taxonomy( 'product_cat' ),
					array(
						'fromType'      => $product_type,
						'toType'        => 'ProductCategory',
						'fromFieldName' => 'categories',
					)
				)
			);
			register_graphql_connection(
				self::get_connection_config(
					get_taxonomy( 'product_tag' ),
					array(
						'fromType'      => $product_type,
						'toType'        => 'ProductTag',
						'fromFieldName' => 'tags',
					)
				)
			);
		}
	}
}
