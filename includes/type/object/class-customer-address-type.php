<?php
/**
 * WPObject Type - Customer_Address_Type
 *
 * Registers WPObject type for WooCommerce customers address object
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Customer_Address_Type
 */
class Customer_Address_Type {

	/**
	 * Registers Customer WPObject type
	 */
	public static function register() {
		register_graphql_object_type(
			'CustomerAddress',
			array(
				'description' => __( 'A customer address object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'firstName' => array(
						'type'        => 'String',
						'description' => __( 'First name', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['first_name'] ) ? $address['first_name'] : null;
						},
					),
					'lastName'  => array(
						'type'        => 'String',
						'description' => __( 'Last name', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['last_name'] ) ? $address['last_name'] : null;
						},
					),
					'company'   => array(
						'type'        => 'String',
						'description' => __( 'Company', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['company'] ) ? $address['company'] : null;
						},
					),
					'address1'  => array(
						'type'        => 'String',
						'description' => __( 'Address 1', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['address_1'] ) ? $address['address_1'] : null;
						},
					),
					'address2'  => array(
						'type'        => 'String',
						'description' => __( 'Address 2', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['address_2'] ) ? $address['address_2'] : null;
						},
					),
					'city'      => array(
						'type'        => 'String',
						'description' => __( 'City', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['city'] ) ? $address['city'] : null;
						},
					),
					'state'     => array(
						'type'        => 'String',
						'description' => __( 'State', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['state'] ) ? $address['state'] : null;
						},
					),
					'postcode'  => array(
						'type'        => 'String',
						'description' => __( 'Zip Postal Code', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['postcode'] ) ? $address['postcode'] : null;
						},
					),
					'country'   => array(
						'type'        => 'CountriesEnum',
						'description' => __( 'Country', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['country'] ) ? $address['country'] : null;
						},
					),
					'email'     => array(
						'type'        => 'String',
						'description' => __( 'E-mail', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['email'] ) ? $address['email'] : null;
						},
					),
					'phone'     => array(
						'type'        => 'String',
						'description' => __( 'Phone', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['phone'] ) ? $address['phone'] : null;
						},
					),
				),
			)
		);
	}
}
