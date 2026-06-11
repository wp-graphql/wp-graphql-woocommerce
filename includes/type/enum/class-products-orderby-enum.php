<?php
/**
 * WPEnum Type - ProductsOrderbyEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Products_Orderby_Enum
 */
class Products_Orderby_Enum extends Post_Type_Orderby_Enum {
	/**
	 * Holds ordering enumeration base name.
	 *
	 * @var string
	 */
	protected static $name = 'Products';

	/**
	 * Return enumeration values related to the "product" post-type.
	 *
	 * @return array
	 */
	protected static function values() {
		return apply_filters(
			'woographql_product_orderby_enum_values',
			array_merge(
				self::post_type_values(),
				[
					'PRICE'         => [
						'value'       => 'price',
						'description' => static function () {
							return __( 'Order by product\'s current price', 'graphql-for-ecommerce' );
						},
					],
					'REGULAR_PRICE' => [
						'value'             => 'price',
						'description'       => static function () {
							return __( 'Order by product\'s regular price', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version. Use "PRICE" instead.', 'graphql-for-ecommerce' ),
					],
					'SALE_PRICE'    => [
						'value'             => 'price',
						'description'       => static function () {
							return __( 'Order by product\'s sale price', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version. Use "PRICE" instead.', 'graphql-for-ecommerce' ),
					],
					'POPULARITY'    => [
						'value'       => 'popularity',
						'description' => static function () {
							return __( 'Order by product popularity', 'graphql-for-ecommerce' );
						},
					],
					'REVIEW_COUNT'  => [
						'value'       => 'comment_count',
						'description' => static function () {
							return __( 'Order by number of reviews on product', 'graphql-for-ecommerce' );
						},
					],
					'RATING'        => [
						'value'       => 'rating',
						'description' => static function () {
							return __( 'Order by product average rating', 'graphql-for-ecommerce' );
						},
					],
					'ON_SALE_FROM'  => [
						'value'             => 'date',
						'description'       => static function () {
							return __( 'Order by date product sale starts', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version.', 'graphql-for-ecommerce' ),
					],
					'ON_SALE_TO'    => [
						'value'             => 'date',
						'description'       => static function () {
							return __( 'Order by date product sale ends', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version.', 'graphql-for-ecommerce' ),
					],
					'TOTAL_SALES'   => [
						'value'             => 'popularity',
						'description'       => static function () {
							return __( 'Order by total sales of products sold', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version. Use "POPULARITY" instead', 'graphql-for-ecommerce' ),
					],
				]
			)
		);
	}
}
