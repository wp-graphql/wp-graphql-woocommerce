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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'CustomerAddress',
			[
				'description' => __( 'A customer address object', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'firstName' => [
						'type'        => 'String',
						'description' => __( 'First name', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['first_name'] ) ? $address['first_name'] : null;
						},
					],
					'lastName'  => [
						'type'        => 'String',
						'description' => __( 'Last name', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['last_name'] ) ? $address['last_name'] : null;
						},
					],
					'company'   => [
						'type'        => 'String',
						'description' => __( 'Company', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['company'] ) ? $address['company'] : null;
						},
					],
					'address1'  => [
						'type'        => 'String',
						'description' => __( 'Address 1', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['address_1'] ) ? $address['address_1'] : null;
						},
					],
					'address2'  => [
						'type'        => 'String',
						'description' => __( 'Address 2', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['address_2'] ) ? $address['address_2'] : null;
						},
					],
					'city'      => [
						'type'        => 'String',
						'description' => __( 'City', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['city'] ) ? $address['city'] : null;
						},
					],
					'state'     => [
						'type'        => 'String',
						'description' => __( 'State', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['state'] ) ? $address['state'] : null;
						},
					],
					'postcode'  => [
						'type'        => 'String',
						'description' => __( 'Zip Postal Code', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['postcode'] ) ? $address['postcode'] : null;
						},
					],
					'country'   => [
						'type'        => 'CountriesEnum',
						'description' => __( 'Country', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['country'] ) ? $address['country'] : null;
						},
					],
					'email'     => [
						'type'        => 'String',
						'description' => __( 'E-mail', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['email'] ) ? $address['email'] : null;
						},
					],
					'phone'     => [
						'type'        => 'String',
						'description' => __( 'Phone', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $address ) {
							return ! empty( $address['phone'] ) ? $address['phone'] : null;
						},
					],
				],
			]
		);
	}
}
