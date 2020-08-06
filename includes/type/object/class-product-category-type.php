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
use WPGraphQL\Data\DataSource;

/**
 * Class - Product_Category_Type
 */
class Product_Category_Type {

	/**
	 * Registers fields to ProductCategory.
	 */
	public static function register_fields() {
		register_graphql_fields(
			'ProductCategory',
			array(
				'image'   => array(
					'type'        => 'MediaItem',
					'description' => __( 'Product category image', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source, array $args, AppContext $context ) {
						$thumbnail_id = get_term_meta( $source->term_id, 'thumbnail_id', true );
						return ! empty( $thumbnail_id )
							? DataSource::resolve_post_object( $thumbnail_id, $context )
							: null;
					},
				),
				'display' => array(
					'type'        => 'ProductCategoryDisplay',
					'description' => __( 'Product category display type', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source, array $args, AppContext $context ) {
						$display = get_term_meta( $source->term_id, 'display_type', true );
						return ! empty( $display ) ? $display : 'default';
					},
				),
				'menuOrder'   => array(
					'type'        => 'Integer',
					'description' => __( 'Product category menu order', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source, array $args, AppContext $context ) {
						$order = get_term_meta( $source->term_id, 'order', true );
						return ! empty( $order ) ? $order : 0;
					},
				),
			)
		);
	}
}
