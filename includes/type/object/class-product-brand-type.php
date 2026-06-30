<?php
/**
 * WPObjectType - ProductBrand
 *
 * Registers fields for the ProductBrand type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   1.0.3
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WPGraphQL\AppContext;

/**
 * Class - Product_Brand_Type
 */
class Product_Brand_Type {
	/**
	 * Registers fields to ProductBrand.
	 *
	 * @return void
	 */
	public static function register_fields() {
		register_graphql_fields(
			'ProductBrand',
			[
				'image' => [
					'type'        => 'MediaItem',
					'description' => static function () {
						return __( 'Product brand image', 'graphql-for-ecommerce' );
					},
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						$thumbnail_id = get_term_meta( $source->term_id, 'thumbnail_id', true );
						return ! empty( $thumbnail_id )
							? $context->get_loader( 'post' )->load_deferred( $thumbnail_id )
							: null;
					},
				],
			]
		);
	}
}
