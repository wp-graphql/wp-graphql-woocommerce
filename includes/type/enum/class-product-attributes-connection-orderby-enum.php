<?php
/**
 * WPEnum Type - Product_Attributes_Connection_Orderby_Enum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Product_Attributes_Connection_Orderby_Enum
 */
class Product_Attributes_Connection_Orderby_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'ProductAttributesConnectionOrderbyEnum',
			[
				'description' => static function () {
					return __( 'Product attributes connection orderby enum', 'graphql-for-ecommerce' );
				},
				'values'      => [
					'NAME'        => [
						'value'       => 'name',
						'description' => static function () {
							return __( 'Order the connection by name.', 'graphql-for-ecommerce' );
						},
					],
					'SLUG'        => [
						'value'       => 'slug',
						'description' => static function () {
							return __( 'Order the connection by slug.', 'graphql-for-ecommerce' );
						},
					],
					'TERM_GROUP'  => [
						'value'       => 'term_group',
						'description' => static function () {
							return __( 'Order the connection by term group.', 'graphql-for-ecommerce' );
						},
					],
					'TERM_ID'     => [
						'value'       => 'term_id',
						'description' => static function () {
							return __( 'Order the connection by term id.', 'graphql-for-ecommerce' );
						},
					],
					'TERM_ORDER'  => [
						'value'       => 'term_order',
						'description' => static function () {
							return __( 'Order the connection by term order.', 'graphql-for-ecommerce' );
						},
					],
					'MENU_ORDER'  => [
						'value'       => 'menu_order',
						'description' => static function () {
							return __( 'Order the connection by woocommerce menu order.', 'graphql-for-ecommerce' );
						},
					],
					'DESCRIPTION' => [
						'value'       => 'description',
						'description' => static function () {
							return __( 'Order the connection by description.', 'graphql-for-ecommerce' );
						},
					],
					'COUNT'       => [
						'value'       => 'count',
						'description' => static function () {
							return __( 'Order the connection by item count.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
