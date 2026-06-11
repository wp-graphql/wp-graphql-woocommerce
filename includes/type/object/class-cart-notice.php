<?php
/**
 * WPObject Type - Cart_Notice
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Cart_Notice
 */
class Cart_Notice {
	/**
	 * Register Cart Notice type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'CartNotice',
			[
				'description' => static function () {
					return __( 'A WooCommerce notice', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'type'    => [
						'type'        => 'CartNoticeTypeEnum',
						'description' => static function () {
							return __( 'Notice type', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $notice ) {
							return $notice['type'] ?? null;
						},
					],
					'message' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Notice message', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $notice ) {
							return $notice['message'] ?? null;
						},
					],
				],
			]
		);
	}
}
