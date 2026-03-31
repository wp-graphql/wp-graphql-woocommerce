<?php
/**
 * WPEnum Type - OrdersOrderbyEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Orders_Orderby_Enum
 */
class Orders_Orderby_Enum extends Post_Type_Orderby_Enum {
	/**
	 * Holds ordering enumeration base name.
	 *
	 * @var string
	 */
	protected static $name = 'Orders';

	/**
	 * Define enumeration values related to the "shop_order" post-type ordering fields.
	 *
	 * @return array
	 */
	protected static function values() {
		return apply_filters(
			'woographql_orderby_enum_values',
			array_merge(
				self::post_type_values(),
				[
					'ORDER_KEY'      => [
						'value'       => '_order_key',
						'description' => static function () {
							return __( 'Order by order key', 'wp-graphql-woocommerce' );
						},
					],
					'DISCOUNT'       => [
						'value'       => '_cart_discount',
						'description' => static function () {
							return __( 'Order by order discount amount', 'wp-graphql-woocommerce' );
						},
					],
					'TOTAL'          => [
						'value'       => '_order_total',
						'description' => static function () {
							return __( 'Order by order total', 'wp-graphql-woocommerce' );
						},
					],
					'TAX'            => [
						'value'       => '_order_tax',
						'description' => static function () {
							return __( 'Order by order total', 'wp-graphql-woocommerce' );
						},
					],
					'DATE_PAID'      => [
						'value'       => '_date_paid',
						'description' => static function () {
							return __( 'Order by date the order was paid', 'wp-graphql-woocommerce' );
						},
					],
					'DATE_COMPLETED' => [
						'value'       => '_date_completed',
						'description' => static function () {
							return __( 'Order by date the order was completed', 'wp-graphql-woocommerce' );
						},
					],
				]
			)
		);
	}
}
