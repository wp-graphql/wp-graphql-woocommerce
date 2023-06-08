<?php
/**
 * WPEnum Type - ProductCategoryDisplay
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.6.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Product_Category_Display
 */
class Product_Category_Display {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'ProductCategoryDisplay',
			[
				'description' => __( 'Product category display type enumeration', 'wp-graphql-woocommerce' ),
				'values'      => [
					'DEFAULT'       => [
						'value'       => 'default',
						'description' => __( 'Display default content connected to this category.', 'wp-graphql-woocommerce' ),
					],
					'PRODUCTS'      => [
						'value'       => 'products',
						'description' => __( 'Display products associated with this category.', 'wp-graphql-woocommerce' ),
					],
					'SUBCATEGORIES' => [
						'value'       => 'subcategories',
						'description' => __( 'Display subcategories of this category.', 'wp-graphql-woocommerce' ),
					],
					'BOTH'          => [
						'value'       => 'both',
						'description' => __( 'Display both products and subcategories of this category.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
