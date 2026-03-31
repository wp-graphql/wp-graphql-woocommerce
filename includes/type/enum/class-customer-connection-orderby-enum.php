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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'CustomerConnectionOrderbyEnum',
			[
				'description' => static function () {
					return __( 'Field to order the connection by', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'ID'              => [
						'value'       => 'ID',
						'description' => static function () {
							return __( 'Order by customer ID', 'wp-graphql-woocommerce' );
						},
					],
					'INCLUDE'         => [
						'value'       => 'include',
						'description' => static function () {
							return __( 'Order by include field', 'wp-graphql-woocommerce' );
						},
					],
					'NAME'            => [
						'value'       => 'display_name',
						'description' => static function () {
							return __( 'Order by customer display name', 'wp-graphql-woocommerce' );
						},
					],
					'USERNAME'        => [
						'value'       => 'username',
						'description' => static function () {
							return __( 'Order by customer username', 'wp-graphql-woocommerce' );
						},
					],
					'EMAIL'           => [
						'value'       => 'email',
						'description' => static function () {
							return __( 'Order by customer email', 'wp-graphql-woocommerce' );
						},
					],
					'REGISTERED_DATE' => [
						'value'       => 'registered',
						'description' => static function () {
							return __( 'Order by customer registration date', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);
	}
}
