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
						'description' => __( 'Shipping rate cost. Includes tax when woocommerce_tax_display_cart is set to incl.', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							$cost = $source->get_cost();
							if ( is_null( $cost ) ) {
								return null;
							}

							if ( 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) {
								$cost = floatval( $cost ) + floatval( $source->get_shipping_tax() );
							}

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $cost;
							}

							return \wc_graphql_price( strval( $cost ) );
						},
					],
					'subtotal'   => [
						'type'        => 'String',
						'description' => __( 'Shipping rate cost before tax.', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							$cost = $source->get_cost();
							if ( is_null( $cost ) ) {
								return null;
							}

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $cost;
							}

							return \wc_graphql_price( $cost );
						},
					],
					'taxTotal'   => [
						'type'        => 'String',
						'description' => __( 'Shipping rate tax total.', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							$tax = $source->get_shipping_tax();

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $tax;
							}

							return \wc_graphql_price( $tax );
						},
					],
				],
			]
		);
	}
}
