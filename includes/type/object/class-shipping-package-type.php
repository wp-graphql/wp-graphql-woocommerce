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
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingPackage',
			array(
				'description' => __( 'Shipping package object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'packageDetails'             => array(
						'type'        => 'String',
						'description' => __( 'Shipping package details', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							$product_names = array();
							foreach ( $source['contents'] as $item_id => $values ) {
								$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
							}

							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
							$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $source );

							return implode( ', ', $product_names );
						},
					),
					'rates'                      => array(
						'type'        => array( 'list_of' => 'ShippingRate' ),
						'description' => __( 'Shipping package rates', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['rates'] ) ? $source['rates'] : null;
						},
					),
					'supportsShippingCalculator' => array(
						'type'        => 'Boolean',
						'description' => __( 'This shipping package supports the shipping calculator.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
							return apply_filters( 'woocommerce_shipping_show_shipping_calculator', true, $source['index'], $source );
						},
					),
				),
			)
		);
	}
}
