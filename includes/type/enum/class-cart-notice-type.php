<?php
/**
 * WPEnum Type - Cart_Notice_Type
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   TBD
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
					return __( 'WooCommerce notice types', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'ERROR'   => [
						'value'       => 'error',
						'description' => static function () {
					return __( 'Error notice', 'wp-graphql-woocommerce' );
				},
					],
					'SUCCESS' => [
						'value'       => 'success',
						'description' => static function () {
					return __( 'Success notice', 'wp-graphql-woocommerce' );
				},
					],
					'NOTICE'  => [
						'value'       => 'notice',
						'description' => static function () {
					return __( 'General notice', 'wp-graphql-woocommerce' );
				},
					],
				],
			]
		);
	}
}
