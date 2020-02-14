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
	 */
	public static function register() {
		register_graphql_object_type(
			'PaymentGateway',
			array(
				'description' => __( 'A payment gateway object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'          => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'gateway\'s title', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					),
					'title'       => array(
						'type'        => 'String',
						'description' => __( 'gateway\'s title', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source->title ) ? $source->title : null;
						},
					),
					'description' => array(
						'type'        => 'String',
						'description' => __( 'gateway\'s description', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source->description ) ? $source->description : null;
						},
					),
					'icon'        => array(
						'type'        => 'String',
						'description' => __( 'gateway\'s icon', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source->icon ) ? $source->icon : null;
						},
					),
				),
			)
		);
	}
}
