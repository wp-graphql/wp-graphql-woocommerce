<?php
/**
 * Registers "registerCustomer" mutation
 *
 * @package WPGraphQL\Extensions\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Mutation\UserRegister;
use WPGraphQL\Extensions\WooCommerce\Data\Mutation\Customer_Mutation;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;
use WPGraphQL\Model\User;

/**
 * Class - Customer_Register
 */
class Customer_Register {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'registerCustomer',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		$input_fields = array_merge(
			UserRegister::get_input_fields(),
			array(
				'billing'               => array(
					'type'        => 'CustomerAddressInput',
					'description' => __( 'Customer billing information', 'wp-graphql-woocommerce' ),
				),
				'shipping'              => array(
					'type'        => 'CustomerAddressInput',
					'description' => __( 'Customer shipping address', 'wp-graphql-woocommerce' ),
				),
				'shippingSameAsBilling' => array(
					'type'        => 'Boolean',
					'description' => __( 'Customer shipping is identical to billing address', 'wp-graphql-woocommerce' ),
				),
			)
		);

		return $input_fields;
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'customer' => array(
				'type'    => 'Customer',
				'resolve' => function ( $payload ) {
					return new Customer( $payload['id'] );
				},
			),
			'viewer'   => array(
				'type'    => 'User',
				'resolve' => function ( $payload ) {
					$user = get_user_by( 'ID', $payload['id'] );
					return new User( $user );
				},
			),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			// Get closure from "UserRegister::mutate_and_get_payload".
			$register_user = UserRegister::mutate_and_get_payload();

			// Register customer with core UserRegister closure.
			$payload = $register_user( $input, $context, $info );

			if ( empty( $payload ) ) {
				throw new UserError( __( 'Failed to update customer.', 'wp-graphql-woocommerce' ) );
			}

			// Map all of the args from GQL to WC friendly.
			$customer_args = Customer_Mutation::prepare_customer_props( $input, 'register' );

			// Create customer object.
			$customer = new \WC_Customer( get_current_user_id() );

			// Set role to customer.
			$customer->set_role( 'customer' );

			// Set billing address.
			if ( ! empty( $customer_args['billing'] ) ) {
				foreach ( $customer_args['billing'] as $prop => $value ) {
					$setter = 'set_billing_' . $prop;
					$customer->{$setter}( $value );
				}
			}

			// Copy billing address as shipping address.
			if ( ! empty( $input['shippingSameAsBilling'] ) && $input['shippingSameAsBilling'] ) {
				$customer_args['shipping'] = array_merge(
					Customer_Mutation::empty_shipping(),
					array_intersect_key( $customer->get_billing( 'edit' ), Customer_Mutation::empty_shipping() )
				);
			}

			// Set shipping address.
			if ( ! empty( $customer_args['shipping'] ) ) {
				foreach ( $customer_args['shipping'] as $prop => $value ) {
					$setter = 'set_shipping_' . $prop;
					$customer->{$setter}( $value );
				}
			}

			// Save customer and get customer ID.
			$customer->save();

			// Return payload.
			return $payload;
		};
	}
}
