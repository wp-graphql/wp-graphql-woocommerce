<?php
/**
 * Defines the "ProductWithDimensions".
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Product_With_Dimension
 */
class Product_With_Dimensions {
	/**
	 * Registers the "ProductWithDimensions" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductWithDimensions',
			array(
				'description' => __( 'A physical product.', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => self::get_fields(),
				'resolveType' => array( Core::class, 'resolve_product_type' ),
			)
		);
	}

	/**
	 * Defines fields of "ProductWithDimensions".
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'id'               => array(
				'type'        => array( 'non_null' => 'ID' ),
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			),
			'databaseId'       => array(
				'type'        => array( 'non_null' => 'Int' ),
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			),
			'weight'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
			),
			'length'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
			),
			'width'            => array(
				'type'        => 'String',
				'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
			),
			'height'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
			),
			'shippingClassId'  => array(
				'type'        => 'Int',
				'description' => __( 'shipping class ID', 'wp-graphql-woocommerce' ),
			),
			'shippingRequired' => array(
				'type'        => 'Boolean',
				'description' => __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' ),
			),
			'shippingTaxable'  => array(
				'type'        => 'Boolean',
				'description' => __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
