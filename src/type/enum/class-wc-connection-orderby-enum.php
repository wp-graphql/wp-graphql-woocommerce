<?php
/**
 * WPEnum Type - WCConnectionOrderbyInput
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

/**
 * Class WC_Connection_Orderby_Enum
 */
class WC_Connection_Orderby_Enum {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_enum_type(
			'WCConnectionOrderbyEnum',
			array(
				'description' => __( 'Field to order the connection by', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'SLUG'          => array(
						'value'       => 'post_name',
						'description' => __( 'Order by slug', 'wp-graphql-woocommerce' ),
					),
					'MODIFIED'      => array(
						'value'       => 'post_modified',
						'description' => __( 'Order by last modified date', 'wp-graphql-woocommerce' ),
					),
					'DATE'          => array(
						'value'       => 'post_date',
						'description' => __( 'Order by publish date', 'wp-graphql-woocommerce' ),
					),
					'PARENT'        => array(
						'value'       => 'post_parent',
						'description' => __( 'Order by parent ID', 'wp-graphql-woocommerce' ),
					),
					'IN'            => array(
						'value'       => 'post__in',
						'description' => __( 'Preserve the ID order given in the IN array', 'wp-graphql-woocommerce' ),
					),
					'NAME_IN'       => array(
						'value'       => 'post_name__in',
						'description' => __( 'Preserve slug order given in the NAME_IN array', 'wp-graphql-woocommerce' ),
					),
					'MENU_ORDER'    => array(
						'value'       => 'menu_order',
						'description' => __( 'Order by the menu order value', 'wp-graphql-woocommerce' ),
					),
					'PRICE'         => array(
						'value'       => '_price',
						'description' => __( 'Order by the menu order value', 'wp-graphql-woocommerce' ),
					),
					'REGULAR_PRICE' => array(
						'value'       => '_regular_price',
						'description' => __( 'Order by the menu order value', 'wp-graphql-woocommerce' ),
					),
					'SALE_PRICE'    => array(
						'value'       => '_sale_price',
						'description' => __( 'Order by the menu order value', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
