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
	 */
	public static function register() {
		register_graphql_input_type(
			'CustomerAddressInput',
			array(
				'description' => __( 'Customer address information', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'firstName' => array(
						'type'        => 'String',
						'description' => __( 'First name', 'wp-graphql-woocommerce' ),
					),
					'lastName'  => array(
						'type'        => 'String',
						'description' => __( 'Last name', 'wp-graphql-woocommerce' ),
					),
					'company'   => array(
						'type'        => 'String',
						'description' => __( 'Company', 'wp-graphql-woocommerce' ),
					),
					'address1'  => array(
						'type'        => 'String',
						'description' => __( 'Address 1', 'wp-graphql-woocommerce' ),
					),
					'address2'  => array(
						'type'        => 'String',
						'description' => __( 'Address 2', 'wp-graphql-woocommerce' ),
					),
					'city'      => array(
						'type'        => 'String',
						'description' => __( 'City', 'wp-graphql-woocommerce' ),
					),
					'state'     => array(
						'type'        => 'String',
						'description' => __( 'State', 'wp-graphql-woocommerce' ),
					),
					'postcode'  => array(
						'type'        => 'String',
						'description' => __( 'Zip Postal Code', 'wp-graphql-woocommerce' ),
					),
					'country'   => array(
						'type'        => 'CountriesEnum',
						'description' => __( 'Country', 'wp-graphql-woocommerce' ),
					),
					'email'     => array(
						'type'        => 'String',
						'description' => __( 'E-mail', 'wp-graphql-woocommerce' ),
					),
					'phone'     => array(
						'type'        => 'String',
						'description' => __( 'Phone', 'wp-graphql-woocommerce' ),
					),
					'overwrite' => array(
						'type'        => 'Boolean',
						'description' => __( 'Clear old address data', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
