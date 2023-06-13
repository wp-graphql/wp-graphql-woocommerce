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
				'description' => __( 'Customer address information', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'firstName' => [
						'type'        => 'String',
						'description' => __( 'First name', 'wp-graphql-woocommerce' ),
					],
					'lastName'  => [
						'type'        => 'String',
						'description' => __( 'Last name', 'wp-graphql-woocommerce' ),
					],
					'company'   => [
						'type'        => 'String',
						'description' => __( 'Company', 'wp-graphql-woocommerce' ),
					],
					'address1'  => [
						'type'        => 'String',
						'description' => __( 'Address 1', 'wp-graphql-woocommerce' ),
					],
					'address2'  => [
						'type'        => 'String',
						'description' => __( 'Address 2', 'wp-graphql-woocommerce' ),
					],
					'city'      => [
						'type'        => 'String',
						'description' => __( 'City', 'wp-graphql-woocommerce' ),
					],
					'state'     => [
						'type'        => 'String',
						'description' => __( 'State', 'wp-graphql-woocommerce' ),
					],
					'postcode'  => [
						'type'        => 'String',
						'description' => __( 'Zip Postal Code', 'wp-graphql-woocommerce' ),
					],
					'country'   => [
						'type'        => 'CountriesEnum',
						'description' => __( 'Country', 'wp-graphql-woocommerce' ),
					],
					'email'     => [
						'type'        => 'String',
						'description' => __( 'E-mail', 'wp-graphql-woocommerce' ),
					],
					'phone'     => [
						'type'        => 'String',
						'description' => __( 'Phone', 'wp-graphql-woocommerce' ),
					],
					'overwrite' => [
						'type'        => 'Boolean',
						'description' => __( 'Clear old address data', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
