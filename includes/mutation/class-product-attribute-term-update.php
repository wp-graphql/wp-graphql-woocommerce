<?php
/**
 * Mutation - updateProductAttributeTerm
 *
 * Registers mutation for updating a product attribute term.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

/**
 * Class Product_Attribute_Term_Update
 */
class Product_Attribute_Term_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateProductAttributeTerm',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ Product_Attribute_Term_Create::class, 'mutate_and_get_payload' ],
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return array_merge(
			Product_Attribute_Term_Create::get_input_fields(),
			[
				'id'   => [
					'type'        => [ 'non_null' => 'Int' ],
					'description' => __( 'The ID of the term to update.', 'wp-graphql-woocommerce' ),
				],
				'name' => [
					'type'        => 'String',
					'description' => __( 'The name of the term.', 'wp-graphql-woocommerce' ),
				],
			]
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'term' => [
				'type'    => 'ProductAttributeTermObject',
				'resolve' => static function ( $payload ) {
					return (object) $payload['term'];
				},
			],
		];
	}
}
