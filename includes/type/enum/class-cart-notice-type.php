<?php
/**
 * WPEnum Type - Cart_Notice_Type
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Cart_Notice_Type
 */
class Cart_Notice_Type {
	/**
	 * Register Cart Notice Type enum
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'CartNoticeTypeEnum',
			[
				'description' => static function () {
					return __( 'WooCommerce notice types', 'graphql-for-ecommerce' );
				},
				'values'      => [
					'ERROR'   => [
						'value'       => 'error',
						'description' => static function () {
							return __( 'Error notice', 'graphql-for-ecommerce' );
						},
					],
					'SUCCESS' => [
						'value'       => 'success',
						'description' => static function () {
							return __( 'Success notice', 'graphql-for-ecommerce' );
						},
					],
					'NOTICE'  => [
						'value'       => 'notice',
						'description' => static function () {
							return __( 'General notice', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
