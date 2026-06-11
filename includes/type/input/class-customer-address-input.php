<?php
/**
 * WPInputObjectType - CustomerAddressInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.1.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Customer_Address_Input
 */
class Customer_Address_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'CustomerAddressInput',
			[
				'description' => static function () {
					return __( 'Customer address information', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'firstName' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'First name', 'graphql-for-ecommerce' );
						},
					],
					'lastName'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Last name', 'graphql-for-ecommerce' );
						},
					],
					'company'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Company', 'graphql-for-ecommerce' );
						},
					],
					'address1'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Address 1', 'graphql-for-ecommerce' );
						},
					],
					'address2'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Address 2', 'graphql-for-ecommerce' );
						},
					],
					'city'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'City', 'graphql-for-ecommerce' );
						},
					],
					'state'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'State', 'graphql-for-ecommerce' );
						},
					],
					'postcode'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Zip Postal Code', 'graphql-for-ecommerce' );
						},
					],
					'country'   => [
						'type'        => 'CountriesEnum',
						'description' => static function () {
							return __( 'Country', 'graphql-for-ecommerce' );
						},
					],
					'email'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'E-mail', 'graphql-for-ecommerce' );
						},
					],
					'phone'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Phone', 'graphql-for-ecommerce' );
						},
					],
					'overwrite' => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Clear old address data', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
