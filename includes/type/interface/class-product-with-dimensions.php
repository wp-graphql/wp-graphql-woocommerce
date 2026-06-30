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
					return __( 'A physical product.', 'graphql-for-ecommerce' );
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
					return __( 'Product or variation global ID', 'graphql-for-ecommerce' );
				},
			],
			'databaseId'       => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'Product or variation ID', 'graphql-for-ecommerce' );
				},
			],
			'weight'           => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s weight', 'graphql-for-ecommerce' );
				},
			],
			'length'           => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s length', 'graphql-for-ecommerce' );
				},
			],
			'width'            => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s width', 'graphql-for-ecommerce' );
				},
			],
			'height'           => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s height', 'graphql-for-ecommerce' );
				},
			],
			'shippingClassId'  => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'shipping class ID', 'graphql-for-ecommerce' );
				},
			],
			'shippingRequired' => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Does product need to be shipped?', 'graphql-for-ecommerce' );
				},
			],
			'shippingTaxable'  => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is product shipping taxable?', 'graphql-for-ecommerce' );
				},
			],
		];
	}
}
