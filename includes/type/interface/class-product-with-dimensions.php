<?php
/**
 * Defines the "ProductWithDimensions".
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

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
			[
				'description' => static function () {
					return __( 'A physical product.', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => 'wc_graphql_resolve_product_type',
			]
		);
	}

	/**
	 * Defines fields of "ProductWithDimensions".
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'               => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => static function () {
					return __( 'Product or variation global ID', 'wp-graphql-woocommerce' );
				},
			],
			'databaseId'       => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'Product or variation ID', 'wp-graphql-woocommerce' );
				},
			],
			'weight'           => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s weight', 'wp-graphql-woocommerce' );
				},
			],
			'length'           => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s length', 'wp-graphql-woocommerce' );
				},
			],
			'width'            => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s width', 'wp-graphql-woocommerce' );
				},
			],
			'height'           => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s height', 'wp-graphql-woocommerce' );
				},
			],
			'shippingClassId'  => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'shipping class ID', 'wp-graphql-woocommerce' );
				},
			],
			'shippingRequired' => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' );
				},
			],
			'shippingTaxable'  => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' );
				},
			],
		];
	}
}
