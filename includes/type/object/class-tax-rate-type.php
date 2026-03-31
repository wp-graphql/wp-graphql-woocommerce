<?php
/**
 * WPObject Type - Tax_Rate_Type
 *
 * Registers TaxRate WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Tax_Rate_Type
 */
class Tax_Rate_Type {
	/**
	 * Registers tax rate type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'TaxRate',
			[
				'description' => static function () {
					return __( 'A Tax rate object', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The globally unique identifier for the tax rate.', 'wp-graphql-woocommerce' );
						},
					],
					'databaseId' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' );
						},
					],
					'country'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Country ISO 3166 code.', 'wp-graphql-woocommerce' );
						},
					],
					'state'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'State code.', 'wp-graphql-woocommerce' );
						},
					],
					'postcode'   => [
						'type'              => 'String',
						'description'       => static function () {
							return __( 'Postcode/ZIP.', 'wp-graphql-woocommerce' );
						},
						'deprecationReason' => 'Use "postcodes" instead.',
					],
					'city'       => [
						'type'              => 'String',
						'description'       => static function () {
							return __( 'City name.', 'wp-graphql-woocommerce' );
						},
						'deprecationReason' => 'Use "cities" instead.',
					],
					'postcodes'  => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Postcodes/ZIPs.', 'wp-graphql-woocommerce' );
						},
					],
					'cities'     => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'City names.', 'wp-graphql-woocommerce' );
						},
					],
					'rate'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax rate.', 'wp-graphql-woocommerce' );
						},
					],
					'name'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax rate name.', 'wp-graphql-woocommerce' );
						},
					],
					'priority'   => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Tax priority.', 'wp-graphql-woocommerce' );
						},
					],
					'compound'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Whether or not this is a compound rate.', 'wp-graphql-woocommerce' );
						},
					],
					'shipping'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Whether or not this tax rate also gets applied to shipping.', 'wp-graphql-woocommerce' );
						},
					],
					'order'      => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Indicates the order that will appear in queries.', 'wp-graphql-woocommerce' );
						},
					],
					'class'      => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Tax class. Default is standard.', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);
	}
}
