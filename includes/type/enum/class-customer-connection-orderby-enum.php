<?php
/**
 * WPEnum Type - CustomerConnectionOrderbyInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.3
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Customer_Connection_Orderby_Enum
 */
class Customer_Connection_Orderby_Enum {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_enum_type(
			'CustomerConnectionOrderbyEnum',
			array(
				'description' => __( 'Field to order the connection by', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'ID'              => array(
						'value'       => 'ID',
						'description' => __( 'Order by customer ID', 'wp-graphql-woocommerce' ),
					),
					'INCLUDE'         => array(
						'value'       => 'include',
						'description' => __( 'Order by include field', 'wp-graphql-woocommerce' ),
					),
					'NAME'            => array(
						'value'       => 'display_name',
						'description' => __( 'Order by customer display name', 'wp-graphql-woocommerce' ),
					),
					'USERNAME'        => array(
						'value'       => 'username',
						'description' => __( 'Order by customer username', 'wp-graphql-woocommerce' ),
					),
					'EMAIL'           => array(
						'value'       => 'email',
						'description' => __( 'Order by customer email', 'wp-graphql-woocommerce' ),
					),
					'REGISTERED_DATE' => array(
						'value'       => 'registered',
						'description' => __( 'Order by customer registration date', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
