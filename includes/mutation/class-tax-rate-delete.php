<?php
/**
 * Mutation - deleteTaxRate
 *
 * Registers mutation for deleting a tax rate.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Utils\Utils;

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
			if ( ! \wc_rest_check_manager_permissions( 'settings', 'delete' ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to delete tax rates.', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
			}
			global $wpdb;
			$id = Utils::get_database_id_from_id( $input['id'] );
			if ( ! $id ) {
				throw new UserError( __( 'Invalid tax rate ID.', 'wp-graphql-woocommerce' ) );
			}

			$tax = $context->get_loader( 'tax_rate' )->load( $id );
			if ( ! $tax ) {
				throw new UserError( __( 'Failed to locate tax rate', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Action before deleting tax rate.
			 *
			 * @param object $tax_rate  The tax rate object.
			 * @param array  $input     Request input.
			 */
			do_action( 'graphql_woocommerce_before_tax_rate_delete', $tax, $input );

			\WC_Tax::_delete_tax_rate( $id );
			if ( 0 === $wpdb->rows_affected ) {
				throw new UserError( __( 'Failed to delete tax rate.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Filter tax rate object before responding.
			 *
			 * @param object $tax_rate  The shipping method object.
			 * @param array  $input     Request input.
			 */
			$tax = apply_filters( 'graphql_woocommerce_tax_rate_delete', $tax, $input );

			return [
				'taxRate' => $tax,
			];
		};
	}
}
