<?php
/**
 * WPObjectType - ProductCategory
 *
 * Registers fields for the ProductCategory type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WPGraphQL\AppContext;

/**
 * Class - Product_Category_Type
 */
class Product_Category_Type {
	/**
	 * Registers fields to ProductCategory.
	 *
	 * @return void
	 */
	public static function register_fields() {
		register_graphql_fields(
			'ProductCategory',
			[
				'image'     => [
					'type'        => 'MediaItem',
					'description' => static function () {
						return __( 'Product category image', 'graphql-for-ecommerce' );
					},
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						$thumbnail_id = get_term_meta( $source->term_id, 'thumbnail_id', true );
						return ! empty( $thumbnail_id )
							? $context->get_loader( 'post' )->load_deferred( $thumbnail_id )
							: null;
					},
				],
				'display'   => [
					'type'        => 'ProductCategoryDisplay',
					'description' => static function () {
						return __( 'Product category display type', 'graphql-for-ecommerce' );
					},
					'resolve'     => static function ( $source ) {
						$display = get_term_meta( $source->term_id, 'display_type', true );
						return ! empty( $display ) ? $display : 'default';
					},
				],
				'menuOrder' => [
					'type'        => 'Integer',
					'description' => static function () {
						return __( 'Product category menu order', 'graphql-for-ecommerce' );
					},
					'resolve'     => static function ( $source ) {
						$order = get_term_meta( $source->term_id, 'order', true );
						return ! empty( $order ) ? $order : 0;
					},
				],
			]
		);
	}
}
