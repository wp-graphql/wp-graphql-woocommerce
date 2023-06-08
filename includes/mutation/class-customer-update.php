<?php
/**
 * Registers "updateCustomer" mutation
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Customer_Mutation;
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\Mutation\UserCreate;
use WPGraphQL\Mutation\UserUpdate;
use WC_Customer;

/**
 * Class - Customer_Update
 */
class Customer_Update {

	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateCustomer',
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
		return array_merge(
			UserCreate::get_input_fields(),
			[
				'id'                    => [
					'type'        => 'ID',
					'description' => __( 'The ID of the user', 'wp-graphql-woocommerce' ),
				],
				'billing'               => [
					'type'        => 'CustomerAddressInput',
					'description' => __( 'Customer billing information', 'wp-graphql-woocommerce' ),
				],
				'shipping'              => [
					'type'        => 'CustomerAddressInput',
					'description' => __( 'Customer shipping address', 'wp-graphql-woocommerce' ),
				],
				'shippingSameAsBilling' => [
					'type'        => 'Boolean',
					'description' => __( 'Customer shipping is identical to billing address', 'wp-graphql-woocommerce' ),
				],
				'metaData'              => [
					'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
					'type'        => [ 'list_of' => 'MetaDataInput' ],
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
			'customer' => [
				'type'    => 'Customer',
				'resolve' => function ( $payload ) {
					return new Customer( $payload['id'] );
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
		return function( $input, AppContext $context, ResolveInfo $info ) {
			$session_only = empty( $input['id'] );
			$payload      = null;

			if ( ! $session_only ) {
				// Get closure from "UserRegister::mutate_and_get_payload".
				$update_user = UserUpdate::mutate_and_get_payload();

				// Update customer with core UserUpdate closure.
				$payload = $update_user( $input, $context, $info );

				if ( empty( $payload ) ) {
					throw new UserError( __( 'Failed to update customer.', 'wp-graphql-woocommerce' ) );
				}
			}

			// Map all of the args from GQL to WC friendly.
			$customer_args = Customer_Mutation::prepare_customer_props( $input, 'update' );

			// Create customer object.
			$customer = ! $session_only ? new WC_Customer( $payload['id'] ) : \WC()->customer;

			// Copy billing address as shipping address.
			if ( isset( $input['shippingSameAsBilling'] ) && $input['shippingSameAsBilling'] ) {
				$customer_args['shipping'] = array_merge(
					Customer_Mutation::empty_shipping(),
					array_intersect_key( $customer->get_billing( 'edit' ), Customer_Mutation::empty_shipping() )
				);
			}

			// Update customer fields.
			foreach ( $customer_args as $prop => $value ) {

				// If field group like 'shipping' or 'billing'.
				if ( ! empty( $value ) && \is_array( $value ) ) {

					// Check if group field has set function and assigns new value.
					foreach ( $value as $field => $field_value ) {
						if ( is_callable( [ $customer, "set_{$prop}_{$field}" ] ) ) {
							$customer->{"set_{$prop}_{$field}"}( $field_value );
						}
					}

					// If field has set function and assigns new value.
				} elseif ( is_callable( [ $customer, "set_{$prop}" ] ) ) {
					$customer->{"set_{$prop}"}( $value );
				}
			}

			// Set meta data.
			if ( ! empty( $input['metaData'] ) ) {
				Customer_Mutation::input_meta_data_mapping( $customer, $input['metaData'] );
			}

			// Save customer and get customer ID.
			$customer->save();

			// Return payload.
			return ! empty( $payload ) ? $payload : [ 'id' => 'session' ];
		};
	}
}
