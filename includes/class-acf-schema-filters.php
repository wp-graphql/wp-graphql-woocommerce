<?php
/**
 * Adds filters that modify WPGraphQL ACF schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.3.0
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\WooCommerce\Model\Coupon;
use WPGraphQL\WooCommerce\Model\Order;
use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class ACF_Schema_Filters
 */
class ACF_Schema_Filters {

	/**
	 * Register filters
	 */
	public static function add_filters() {
		// Registers WooCommerce CPTs && taxonomies.
		add_filter( 'graphql_acf_get_root_id', array( __CLASS__, 'resolve_crud_root_id' ), 10, 2 );
		add_filter( 'graphql_acf_post_object_source', array( __CLASS__, 'resolve_post_object_source' ), 10, 2 );
	}

	/**
	 * Resolve post object ID from CRUD object Model.
	 *
	 * @param integer|null $id    Post object database ID.
	 * @param mixed        $root  Root resolver.
	 *
	 * @return integer|null
	 */
	public static function resolve_crud_root_id( $id, $root ) {
		switch ( true ) {
			case $root instanceof \WPGraphQL\WooCommerce\Model\CRUD_CPT:
				$id = absint( $root->ID );
				break;
		}

		return $id;
	}

	/**
	 * Filters ACF "post_object" field type resolver to ensure that
	 * the proper Type source is provided for WooCommerce CPTs.
	 *
	 * @param mixed|null $source  source of the data being provided.
	 * @param mixed|null $value  Post ID.
	 *
	 * @return mixed|null
	 */
	public static function resolve_post_object_source( $source, $value ) {
		// Bail if $value is empty to prevent an unexpected query result.
		if ( empty( $value ) ) {
			return $source;
		}
		
		$post = get_post( $value );
		if ( $post instanceof \WP_Post ) {
			switch ( $post->post_type ) {
				case 'shop_coupon':
					$source = new Coupon( $post->ID );
					break;
				case 'shop_order':
					$source = new Order( $post->ID );
					break;
				case 'product':
					$source = new Product( $post->ID );
					break;
			}
		}

		return $source;
	}
}
