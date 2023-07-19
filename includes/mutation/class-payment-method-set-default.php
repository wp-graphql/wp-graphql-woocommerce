<?php
/**
 * Mutation - setDefaultPaymentMethod
 *
 * Registers mutation for changing user's preferred payment method.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.12.4
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WC_Payment_Tokens;
use WPGraphQL\AppContext;

/**
 * Class Payment_Method_Set_Default
 */
class Payment_Method_Set_Default {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'setDefaultPaymentMethod',
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
			'tokenId' => [
				'type'        => [ 'non_null' => 'Integer' ],
				'description' => __( 'Token ID of the payment token being deleted.', 'wp-graphql-woocommerce' ),
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
			'status' => [
				'type'        => 'String',
				'description' => __( 'Status of the request', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					return ! empty( $payload['status'] ) ? $payload['status'] : 'FAILED';
				},
			],
			'token'  => [
				'type'        => 'PaymentToken',
				'description' => __( 'Preferred payment method token', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					return ! empty( $payload['token'] ) ? $payload['token'] : null;
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
			global $wp;
			if ( ! is_user_logged_in() ) {
				throw new UserError( __( 'Must be authenticated to set a default payment method', 'wp-graphql-woocommerce' ) );
			}

			$token_id = $input['tokenId'];
			$token    = WC_Payment_Tokens::get( $token_id );

			if ( is_null( $token ) || get_current_user_id() !== $token->get_user_id() ) {
				throw new UserError( __( 'Invalid payment method.', 'wp-graphql-woocommerce' ) );
			}

			WC_Payment_Tokens::set_users_default( $token->get_user_id(), intval( $token_id ) );
			wc_add_notice( __( 'This payment method was successfully set as your default.', 'wp-graphql-woocommerce' ) );

			return [
				'status' => 'SUCCESS',
				'token'  => WC_Payment_Tokens::get( $token_id ),
			];
		};
	}
}
