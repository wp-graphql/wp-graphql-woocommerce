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
					return __( 'A Tax rate object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The globally unique identifier for the tax rate.', 'graphql-for-ecommerce' );
						},
					],
					'databaseId' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'The ID of the customer in the database', 'graphql-for-ecommerce' );
						},
					],
					'country'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Country ISO 3166 code.', 'graphql-for-ecommerce' );
						},
					],
					'state'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'State code.', 'graphql-for-ecommerce' );
						},
					],
					'postcode'   => [
						'type'              => 'String',
						'description'       => static function () {
							return __( 'Postcode/ZIP.', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => 'Use "postcodes" instead.',
					],
					'city'       => [
						'type'              => 'String',
						'description'       => static function () {
							return __( 'City name.', 'graphql-for-ecommerce' );
						},
						'deprecationReason' => 'Use "cities" instead.',
					],
					'postcodes'  => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Postcodes/ZIPs.', 'graphql-for-ecommerce' );
						},
					],
					'cities'     => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'City names.', 'graphql-for-ecommerce' );
						},
					],
					'rate'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax rate.', 'graphql-for-ecommerce' );
						},
					],
					'name'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax rate name.', 'graphql-for-ecommerce' );
						},
					],
					'priority'   => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Tax priority.', 'graphql-for-ecommerce' );
						},
					],
					'compound'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Whether or not this is a compound rate.', 'graphql-for-ecommerce' );
						},
					],
					'shipping'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Whether or not this tax rate also gets applied to shipping.', 'graphql-for-ecommerce' );
						},
					],
					'order'      => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Indicates the order that will appear in queries.', 'graphql-for-ecommerce' );
						},
					],
					'class'      => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Tax class. Default is standard.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
