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
		return array_merge(
			self::post_type_values(),
			array(
				'PRICE'         => array(
					'value'       => '_price',
					'description' => __( 'Order by product\'s current price', 'wp-graphql-woocommerce' ),
				),
				'REGULAR_PRICE' => array(
					'value'       => '_regular_price',
					'description' => __( 'Order by product\'s regular price', 'wp-graphql-woocommerce' ),
				),
				'SALE_PRICE'    => array(
					'value'       => '_sale_price',
					'description' => __( 'Order by product\'s sale price', 'wp-graphql-woocommerce' ),
				),
				'REVIEW_COUNT'  => array(
					'value'       => '_wc_rating_count',
					'description' => __( 'Order by number of reviews on product', 'wp-graphql-woocommerce' ),
				),
				'RATING'        => array(
					'value'       => '_wc_average_rating',
					'description' => __( 'Order by product average rating', 'wp-graphql-woocommerce' ),
				),
				'ON_SALE_FROM'  => array(
					'value'       => '_sale_price_dates_from',
					'description' => __( 'Order by date product sale starts', 'wp-graphql-woocommerce' ),
				),
				'ON_SALE_TO'    => array(
					'value'       => '_sale_price_dates_to',
					'description' => __( 'Order by date product sale ends', 'wp-graphql-woocommerce' ),
				),
				'TOTAL_SALES'   => array(
					'value'       => 'total_sales',
					'description' => __( 'Order by total sales of products sold', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}
}
