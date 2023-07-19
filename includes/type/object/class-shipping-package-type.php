<?php
/**
 * WPObject Type - Shipping_Package_Type
 *
 * Registers ShippingPackage WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Shipping_Package_Type
 */
class Shipping_Package_Type {
	/**
	 * Registers type.
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingPackage',
			[
				'description' => __( 'Shipping package object', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'packageDetails'             => [
						'type'        => 'String',
						'description' => __( 'Shipping package details', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$product_names = [];
							foreach ( $source['contents'] as $item_id => $values ) {
								$product_names[ $item_id ] = html_entity_decode( $values['data']->get_name() . ' &times;' . $values['quantity'] );
							}

							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
							$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $source );

							return implode( ', ', $product_names );
						},
					],
					'rates'                      => [
						'type'        => [ 'list_of' => 'ShippingRate' ],
						'description' => __( 'Shipping package rates', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['rates'] ) ? $source['rates'] : null;
						},
					],
					'supportsShippingCalculator' => [
						'type'        => 'Boolean',
						'description' => __( 'This shipping package supports the shipping calculator.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
							return apply_filters( 'woocommerce_shipping_show_shipping_calculator', true, $source['index'], $source );
						},
					],
				],
			]
		);
	}
}
