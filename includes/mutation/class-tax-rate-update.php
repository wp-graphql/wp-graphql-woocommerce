<?php
/**
 * Mutation - updateTaxRate
 *
 * Registers mutation for updating a tax rate.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use WPGraphQL\AppContext;

/**
 * Class - Tax_Rate_Update
 */
class Tax_Rate_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateTaxRate',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ Tax_Rate_Create::class, 'mutate_and_get_payload' ],
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
			Tax_Rate_Create::get_input_fields(),
			[
				'id' => [
					'type'        => [ 'non_null' => 'Int' ],
					'description' => __( 'The ID of the tax rate to update.', 'wp-graphql-woocommerce' ),
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
			'taxRate' => [
				'type'    => 'TaxRate',
				'resolve' => static function ( array $payload, array $args, AppContext $context ) {
					return $context->get_loader( 'tax_rate' )->load( $payload['tax_rate_id'] );
				},
			],
		];
	}
}
