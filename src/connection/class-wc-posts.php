<?php
/**
 * Connection type - WC_Posts
 *
 * Registers connections to WC_Posts
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Connection\PostObjects;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - WC_Posts
 */
class WC_Posts extends PostObjects {
	/**
	 * Registers the various connections from other Types to WooCommerce post-types
	 */
	public static function register_connections() {
		/**
		 * To coupon connections
		 */

		/**
		 * To product connections
		 */
		$post_type_object = get_post_type_object( 'product' );
		register_graphql_connection(
			self::get_connection_config(
				$post_type_object,
				array(
					'fromType'      => 'Product',
					'toType'        => 'Product',
					'fromFieldName' => 'upsell',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				$post_type_object,
				array(
					'fromType'      => 'Product',
					'toType'        => 'Product',
					'fromFieldName' => 'crossSell',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				$post_type_object,
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'Product',
					'fromFieldName' => 'products',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				$post_type_object,
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'Product',
					'fromFieldName' => 'excludedProducts',
				)
			)
		);

		/**
		 * To product variation connections
		 */
		$post_type_object = get_post_type_object( 'product_variation' );
		register_graphql_connection(
			self::get_connection_config(
				$post_type_object,
				array(
					'fromType'      => 'Product',
					'toType'        => 'ProductVariation',
					'fromFieldName' => 'variations',
				)
			)
		);

		/**
		 * To attachment connections
		 */
		$post_type_object = get_post_type_object( 'attachment' );
		register_graphql_connection(
			self::get_connection_config(
				$post_type_object,
				array(
					'fromType'      => 'Product',
					'toType'        => 'MediaItem',
					'fromFieldName' => 'galleryImages',
				)
			)
		);

		/**
		 * To order connections
		 */

		/**
		 * OrderRefund connections
		 */
	}

	/**
	 * Retrieve connection_args for specified post-type
	 *
	 * @param string $post_type - Connection target post-type.
	 *
	 * @return array
	 */
	public static function get_connection_args( $post_type = '' ) {
		switch ( $post_type ) {
			case 'shop_coupon':
				$args = array();
				break;

			case 'product':
			case 'product_variation':
				$args = array();
				break;

			case 'shop_order':
				$args = array();
				break;

			case 'shop_order_refund':
				$args = array();
				break;

			default:
				$args = array();
		}

		return array_merge(
			parent::get_connection_args(),
			$args
		);
	}
}
