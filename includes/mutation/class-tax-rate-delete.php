<?php
/**
 * Mutation - deleteTaxRate
 *
 * Registers mutation for deleting a tax rate.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Tax_Rate;

/**
 * Class - Tax_Rate_Delete
 */
class Tax_Rate_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteTaxRate',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'id' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the tax rate to update.', 'wp-graphql-woocommerce' ),
			],
		];
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
				'resolve' => static function ( $payload ) {
					return $payload['taxRate'];
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			global $wpdb;
			$id = $input['id'];

			$tax = $context->get_loader( 'tax_rate' )->load( $id );
			if ( ! $tax ) {
				throw new UserError( __( 'Invalid tax rate ID.', 'wp-graphql-woocommerce' ) );
			}

			\WC_Tax::_delete_tax_rate( $id );
			if ( 0 === $wpdb->rows_affected ) {
				throw new UserError( __( 'Failed to delete tax rate.', 'wp-graphql-woocommerce' ) );
			}

			return [
				'taxRate' => $tax,
			];
		};
	}
}
