<?php
/**
 * WPObject Type - Cart_Notice
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
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
				'description' => __( 'A WooCommerce notice', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'type'    => [
						'type'        => 'CartNoticeTypeEnum',
						'description' => __( 'Notice type', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $notice ) {
							return $notice['type'] ?? null;
						},
					],
					'message' => [
						'type'        => 'String',
						'description' => __( 'Notice message', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $notice ) {
							return $notice['message'] ?? null;
						},
					],
				],
			]
		);
	}
}
