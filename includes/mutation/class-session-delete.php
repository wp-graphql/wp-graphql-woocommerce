<?php
/**
 * Mutation - forgetSession
 *
 * Registers mutation for deleting sessions from the DB.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.21.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Session_Delete
 */
class Session_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'forgetSession',
			[
				'inputFields'         => [],
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
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
			'session' => [
				'type'    => [ 'list_of' => 'MetaData' ],
				'resolve' => static function ( $payload ) {
					// Guard against missing session data.
					if ( empty( $payload['session'] ) ) {
						return [];
					}

					// Prepare session data.
					$session = [];
					foreach ( $payload['session'] as $key => $value ) {
						$meta        = new \stdClass();
						$meta->id    = null;
						$meta->key   = $key;
						$meta->value = maybe_unserialize( $value );
						$session[]   = $meta;
					}

					return $session;
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
		return static function ( $input ) {
			Cart_Mutation::check_session_token();

			/**
			 * Session handler.
			 *
			 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
			 */
			$session = \WC()->session;

			// Get session data.
			$session_data = $session->get_session_data();
			do_action( 'woographql_before_forget_session', $session_data, $input, $session );

			// Clear session data.
			$session->forget_session();

			do_action( 'woographql_after_forget_session', $session_data, $input, $session );

			// Return payload.
			return [ 'session' => $session_data ];
		};
	}
}
