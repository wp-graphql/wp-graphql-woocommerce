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
				'description' => __( 'Product attributes connection orderby enum', 'wp-graphql-woocommerce' ),
				'values'      => [
					'NAME'        => [
						'value'       => 'name',
						'description' => __( 'Order the connection by name.', 'wp-graphql-woocommerce' ),
					],
					'SLUG'        => [
						'value'       => 'slug',
						'description' => __( 'Order the connection by slug.', 'wp-graphql-woocommerce' ),
					],
					'TERM_GROUP'  => [
						'value'       => 'term_group',
						'description' => __( 'Order the connection by term group.', 'wp-graphql-woocommerce' ),
					],
					'TERM_ID'     => [
						'value'       => 'term_id',
						'description' => __( 'Order the connection by term id.', 'wp-graphql-woocommerce' ),
					],
					'TERM_ORDER'  => [
						'value'       => 'term_order',
						'description' => __( 'Order the connection by term order.', 'wp-graphql-woocommerce' ),
					],
					'MENU_ORDER'  => [
						'value'       => 'menu_order',
						'description' => __( 'Order the connection by woocommerce menu order.', 'wp-graphql-woocommerce' ),
					],
					'DESCRIPTION' => [
						'value'       => 'description',
						'description' => __( 'Order the connection by description.', 'wp-graphql-woocommerce' ),
					],
					'COUNT'       => [
						'value'       => 'count',
						'description' => __( 'Order the connection by item count.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
