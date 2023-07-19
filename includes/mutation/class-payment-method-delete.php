<?php
/**
 * Mutation - deletePaymentMethod
 *
 * Registers mutation for deleting a stored payment method.
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
 * Class Payment_Method_Delete
 */
class Payment_Method_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deletePaymentMethod',
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

			WC_Payment_Tokens::delete( $token_id );
			wc_add_notice( __( 'Payment method deleted.', 'wp-graphql-woocommerce' ) );

			return [ 'status' => 'SUCCESS' ];
		};
	}
}
