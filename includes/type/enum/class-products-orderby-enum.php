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
						'description' => __( 'Order by product\'s current price', 'wp-graphql-woocommerce' ),
					],
					'REGULAR_PRICE' => [
						'value'             => 'price',
						'description'       => __( 'Order by product\'s regular price', 'wp-graphql-woocommerce' ),
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version. Use "PRICE" instead.', 'wp-graphql-woocommerce' ),
					],
					'SALE_PRICE'    => [
						'value'             => 'price',
						'description'       => __( 'Order by product\'s sale price', 'wp-graphql-woocommerce' ),
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version. Use "PRICE" instead.', 'wp-graphql-woocommerce' ),
					],
					'POPULARITY'    => [
						'value'       => 'popularity',
						'description' => __( 'Order by product popularity', 'wp-graphql-woocommerce' ),
					],
					'REVIEW_COUNT'  => [
						'value'       => 'comment_count',
						'description' => __( 'Order by number of reviews on product', 'wp-graphql-woocommerce' ),
					],
					'RATING'        => [
						'value'       => 'rating',
						'description' => __( 'Order by product average rating', 'wp-graphql-woocommerce' ),
					],
					'ON_SALE_FROM'  => [
						'value'             => 'date',
						'description'       => __( 'Order by date product sale starts', 'wp-graphql-woocommerce' ),
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version.', 'wp-graphql-woocommerce' ),
					],
					'ON_SALE_TO'    => [
						'value'             => 'date',
						'description'       => __( 'Order by date product sale ends', 'wp-graphql-woocommerce' ),
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version.', 'wp-graphql-woocommerce' ),
					],
					'TOTAL_SALES'   => [
						'value'             => 'popularity',
						'description'       => __( 'Order by total sales of products sold', 'wp-graphql-woocommerce' ),
						'deprecationReason' => __( 'This field is deprecated and will be removed in a future version. Use "POPULARITY" instead', 'wp-graphql-woocommerce' ),
					],
				]
			)
		);
	}
}
