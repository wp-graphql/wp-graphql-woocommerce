<?php
/**
 * WPObject Type - Payment_Gateway_Type
 *
 * Registers PaymentGateway WPObject type.
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Payment_Gateway_Type
 */
class Payment_Gateway_Type {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'PaymentGateway',
			[
				'description' => __( 'A payment gateway object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'gateway\'s title', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					],
					'title'       => [
						'type'        => 'String',
						'description' => __( 'gateway\'s title', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->title ) ? $source->title : null;
						},
					],
					'description' => [
						'type'        => 'String',
						'description' => __( 'gateway\'s description', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->description ) ? $source->description : null;
						},
					],
					'icon'        => [
						'type'        => 'String',
						'description' => __( 'gateway\'s icon', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->icon ) ? $source->icon : null;
						},
					],
				],
			]
		);
	}
}
