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

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

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
				'description' => __( 'A Tax rate object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The globally unique identifier for the tax rate.', 'wp-graphql-woocommerce' ),
					],
					'databaseId' => [
						'type'        => 'Int',
						'description' => __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' ),
					],
					'country'    => [
						'type'        => 'String',
						'description' => __( 'Country ISO 3166 code.', 'wp-graphql-woocommerce' ),
					],
					'state'      => [
						'type'        => 'String',
						'description' => __( 'State code.', 'wp-graphql-woocommerce' ),
					],
					'postcode'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'Postcode/ZIP.', 'wp-graphql-woocommerce' ),
					],
					'city'       => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'City name.', 'wp-graphql-woocommerce' ),
					],
					'rate'       => [
						'type'        => 'String',
						'description' => __( 'Tax rate.', 'wp-graphql-woocommerce' ),
					],
					'name'       => [
						'type'        => 'String',
						'description' => __( 'Tax rate name.', 'wp-graphql-woocommerce' ),
					],
					'priority'   => [
						'type'        => 'Int',
						'description' => __( 'Tax priority.', 'wp-graphql-woocommerce' ),
					],
					'compound'   => [
						'type'        => 'Boolean',
						'description' => __( 'Whether or not this is a compound rate.', 'wp-graphql-woocommerce' ),
					],
					'shipping'   => [
						'type'        => 'Boolean',
						'description' => __( 'Whether or not this tax rate also gets applied to shipping.', 'wp-graphql-woocommerce' ),
					],
					'order'      => [
						'type'        => 'Int',
						'description' => __( 'Indicates the order that will appear in queries.', 'wp-graphql-woocommerce' ),
					],
					'class'      => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'Tax class. Default is standard.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
