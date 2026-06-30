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
				'description' => static function () {
					return __( 'A payment gateway object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'gateway\'s title', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					],
					'title'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'gateway\'s title', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->title ) ? $source->title : null;
						},
					],
					'description' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'gateway\'s description', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->description ) ? $source->description : null;
						},
					],
					'icon'        => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'gateway\'s icon', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->icon ) ? $source->icon : null;
						},
					],
				],
			]
		);
	}
}
