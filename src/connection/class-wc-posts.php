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

class WC_Posts {
	/**
	 * Registers the various connections from other Types to Product
	 */
	public static function register_connections() {
		/**
		 * To coupon connections
		 */

		/**
		 * To product connections
		 */
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'toType'        => 'Product',
					'fromFieldName' => 'upsell',
				),
				'product'
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'toType'        => 'Product',
					'fromFieldName' => 'crossSell',
				),
				'product'
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'Product',
					'fromFieldName' => 'products',
				),
				'product'
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'Product',
					'fromFieldName' => 'excludedProducts',
				),
				'product'
			)
		);

		/**
		 * To product variation connections
		 */
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'toType'        => 'ProductVariation',
					'fromFieldName' => 'variations',
				),
				'product_variation'
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
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args Connection configuration
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = array(), $post_type ) {
		$connection_args = array_merge(
			PostObjects::get_connection_args(),
			self::get_connection_args( $post_type )
		);

		$defaults = array(
			'queryClass'       => 'WP_Query',
			'connectionFields' => array(
				'postTypeInfo' => array(
					'type'        => 'PostType',
					'description' => __( 'Information about the type of content being queried', 'wp-graphql-woocommerce' ),
					'resolve'     => function ( $source, array $args, $context, $info ) use( $post_type ) {
						return DataSource::resolve_post_type( $post_type );
					},
				),
			),
			'resolveNode'      => function( $id, $args, $context, $info ) use( $post_type ) {
				return DataSource::resolve_post_object( $id, $context, $post_type );
			},
			'connectionArgs'   => $connection_args,
			'resolve'          => function ( $root, $args, $context, $info ) use( $post_type ) {
				return Factory::resolve_wc_posts_connection( $root, $args, $context, $info, $post_type );
			},
		);

		return array_merge( $defaults, $args );
	}

	public static function get_connection_args( $post_type ) {
		switch( $post_type ) {
			case 'shop_coupon':
				return array(

				);
				break;

			case 'product':
			case 'product_variation':
				return array(

				);
				break;

			case 'shop_order':
				return array(

				);
				break;
				
			case 'shop_order_refund':
				return array(

				);
				break;
			default:
				return array();
		}
	}
}