<?php
/**
 * Defines the fields for physical products.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Products_With_Dimensions
 */
class Products_With_Dimensions {
	/**
	 * Registers the "ProductsWithDimensions" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductsWithDimensions',
			[
				'description' => __( 'Physical products.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines "ProductsWithDimensions" fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'               => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			],
			'databaseId'       => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			],
			'weight'           => [
				'type'        => 'String',
				'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
			],
			'length'           => [
				'type'        => 'String',
				'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
			],
			'width'            => [
				'type'        => 'String',
				'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
			],
			'height'           => [
				'type'        => 'String',
				'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
			],
			'shippingClassId'  => [
				'type'        => 'Int',
				'description' => __( 'shipping class ID', 'wp-graphql-woocommerce' ),
			],
			'shippingRequired' => [
				'type'        => 'Boolean',
				'description' => __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' ),
			],
			'shippingTaxable'  => [
				'type'        => 'Boolean',
				'description' => __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' ),
			],
		];
	}
}
