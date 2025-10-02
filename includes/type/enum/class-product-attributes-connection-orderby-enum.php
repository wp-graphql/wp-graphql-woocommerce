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
			array(
				'description' => __( 'Product attributes connection orderby enum', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'NAME'        => array(
						'value'       => 'name',
						'description' => __( 'Order the connection by name.', 'wp-graphql-woocommerce' ),
					),
					'SLUG'        => array(
						'value'       => 'slug',
						'description' => __( 'Order the connection by slug.', 'wp-graphql-woocommerce' ),
					),
					'TERM_GROUP'  => array(
						'value'       => 'term_group',
						'description' => __( 'Order the connection by term group.', 'wp-graphql-woocommerce' ),
					),
					'TERM_ID'     => array(
						'value'       => 'term_id',
						'description' => __( 'Order the connection by term id.', 'wp-graphql-woocommerce' ),
					),
					'TERM_ORDER'  => array(
						'value'       => 'term_order',
						'description' => __( 'Order the connection by term order.', 'wp-graphql-woocommerce' ),
					),
					'MENU_ORDER'  => array(
						'value'       => 'menu_order',
						'description' => __( 'Order the connection by woocommerce menu order.', 'wp-graphql-woocommerce' ),
					),
					'DESCRIPTION' => array(
						'value'       => 'description',
						'description' => __( 'Order the connection by description.', 'wp-graphql-woocommerce' ),
					),
					'COUNT'       => array(
						'value'       => 'count',
						'description' => __( 'Order the connection by item count.', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
