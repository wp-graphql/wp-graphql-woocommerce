<?php
/**
 * Mutation - updateSession
 *
 * Registers mutation for updating session meta data.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.12.5
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;
use WPGraphQL\WooCommerce\Model\Customer;

/**
 * Class - Update_Session
 */
class Update_Session {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateSession',
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
			'sessionData' => [
				'type'        => [ 'list_of' => 'MetaDataInput' ],
				'description' => __( 'Data to be persisted in the session.', 'wp-graphql-woocommerce' ),
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
			'session'  => [
				'type'    => [ 'list_of' => 'MetaData' ],
				'resolve' => static function ( $payload ) {
					/**
					 * Session handler.
					 *
					 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
					 */
					$session      = \WC()->session;
					$session_data = $session->get_session_data();
					$session      = [];
					foreach ( $session_data as $key => $value ) {
						$meta        = new \stdClass();
						$meta->id    = null;
						$meta->key   = $key;
						$meta->value = maybe_unserialize( $value );
						$session[]   = $meta;
					}

					return $session;
				},
			],
			'customer' => [
				'type'    => 'Customer',
				'resolve' => static function () {
					return new Customer( 'session' );
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
			Cart_Mutation::check_session_token();

			// Guard against missing input.
			if ( empty( $input['sessionData'] ) ) {
				throw new UserError( __( 'No session data provided', 'wp-graphql-woocommerce' ) );
			}
			$session_data_input = $input['sessionData'];

			/**
			 * Session handler.
			 *
			 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
			 */
			$session = \WC()->session;

			// Save session data input.
			foreach ( $session_data_input as $meta ) {
				$session->set( $meta['key'], $meta['value'] );
			}

			if ( is_a( $session, '\WC_Session_Handler' ) ) {
				$session->save_data();
			}

			do_action( 'woographql_update_session', true );

			// Process errors or return successful.
			$notices = $session->get( 'wc_notices' );
			if ( ! empty( $notices['error'] ) ) {
				$error_messages = implode( ' ', array_column( $notices['error'], 'notice' ) );
				\wc_clear_notices();
				throw new UserError( $error_messages );
			} else {
				return [ 'status' => 'SUCCESS' ];
			}
		};
	}
}
