<?php
/**
 * Registers "registerCustomer" mutation
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\UserMutation;
use WPGraphQL\WooCommerce\Data\Mutation\Customer_Mutation;
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\Model\User;
use WPGraphQL\Mutation\UserRegister;
use WC_Customer;

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
		$result = array_merge(
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

		// Make the username field optional.
    	$result['username']['type'] = 'String';

    	return $result;
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
			// Validate input.
			if ( empty( $input['email'] ) ) {
				throw new UserError( __( 'Please provide a valid email address.', 'wp-graphql-woocommerce' ) );
			}

			// Validate password input.
			if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) && empty( $input['password'] ) ) {
				throw new UserError(
					__(
						'A password was not provided and WooCommerce does not automatically generate one for you.',
						'wp-graphql-woocommerce'
					)
				);
			}

			// Map all of the args from GQL to WP friendly.
			$user_args = UserMutation::prepare_user_object( $input, 'registerCustomer' );

			// Create the user using native WooCommerce function.
			$user_id = \wc_create_new_customer(
				$user_args['user_email'],
				isset( $user_args['user_login'] ) ? $user_args['user_login'] : '',
				isset( $user_args['user_pass'] ) ? $user_args['user_pass'] : '',
				$user_args
			);

			// Throw an exception if the user failed to register.
			if ( is_wp_error( $user_id ) ) {
				if ( ! empty( $user_id->get_error_message() ) ) {
					throw new UserError( $user_id->get_error_message() );
				}

				throw new UserError(
					__( 'Sorry, an unknown error occured while trying to register customer', 'wp-graphql-woocommerce' )
				);
			}

			// If the $post_id is empty, we should throw an exception.
			if ( empty( $user_id ) ) {
				throw new UserError( __( 'The object failed to create', 'wp-graphql-woocommerce' ) );
			}

			// Update additional user data.
			UserMutation::update_additional_user_object_data( $user_id, $input, 'registerCustomer', $context, $info );

			// Map all of the args from GQL to WC friendly.
			$customer_args = Customer_Mutation::prepare_customer_props( $input, 'register' );

			// Create customer object.
			$customer = new WC_Customer( $user_id );

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

			// Update current user.
			if ( ! is_user_logged_in() ) {
				wp_set_current_user( $user_id );
			}

			// Return payload.
			return array( 'id' => $user_id );
		};
	}
}
