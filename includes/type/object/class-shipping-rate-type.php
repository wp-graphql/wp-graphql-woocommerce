<?php
/**
 * WPObject Type - Shipping_Rate_Type
 *
 * Registers ShippingRate WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Shipping_Rate_Type
 */
class Shipping_Rate_Type {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingRate',
			[
				'description' => __( 'Shipping rate object', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Shipping rate ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->get_id() ) ? $source->get_id() : null;
						},
					],
					'methodId'   => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Shipping method ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->get_method_id() ) ? $source->get_method_id() : null;
						},
					],
					'instanceId' => [
						'type'        => 'Int',
						'description' => __( 'Shipping instance ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->get_instance_id() ) ? $source->get_instance_id() : null;
						},
					],
					'label'      => [
						'type'        => 'String',
						'description' => __( 'Shipping rate label', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->get_label() ) ? $source->get_label() : null;
						},
					],
					'cost'       => [
						'type'        => 'String',
						'description' => __( 'Shipping rate cost', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->get_cost() ) ? $source->get_cost() : null;
						},
					],
				],
			]
		);
	}
}
