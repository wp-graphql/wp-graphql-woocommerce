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
					return __( 'Field to order the connection by', 'graphql-for-ecommerce' );
				},
				'values'      => [
					'ID'              => [
						'value'       => 'ID',
						'description' => static function () {
							return __( 'Order by customer ID', 'graphql-for-ecommerce' );
						},
					],
					'INCLUDE'         => [
						'value'       => 'include',
						'description' => static function () {
							return __( 'Order by include field', 'graphql-for-ecommerce' );
						},
					],
					'NAME'            => [
						'value'       => 'display_name',
						'description' => static function () {
							return __( 'Order by customer display name', 'graphql-for-ecommerce' );
						},
					],
					'USERNAME'        => [
						'value'       => 'username',
						'description' => static function () {
							return __( 'Order by customer username', 'graphql-for-ecommerce' );
						},
					],
					'EMAIL'           => [
						'value'       => 'email',
						'description' => static function () {
							return __( 'Order by customer email', 'graphql-for-ecommerce' );
						},
					],
					'REGISTERED_DATE' => [
						'value'       => 'registered',
						'description' => static function () {
							return __( 'Order by customer registration date', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
