<?php
/**
 * Mutation - updateProduct
 *
 * Registers mutation for updating a product.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 1.0.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class Product_Update
 */
class Product_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateProduct',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ Product_Create::class, 'mutate_and_get_payload' ],
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
			Product_Create::get_input_fields(),
			[
				'id'   => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => static function () {
						return __( 'Unique identifier for the product.', 'wp-graphql-woocommerce' );
					},
				],
				'name' => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Name of the product.', 'wp-graphql-woocommerce' );
					},
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
			'product' => [
				'type'    => 'Product',
				'resolve' => static function ( $payload ) {
					return new Product( $payload['id'] );
				},
			],
		];
	}
}
