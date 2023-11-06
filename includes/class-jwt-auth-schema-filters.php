<?php
/**
 * Adds filters that modify the WooGraphQL schema to include WPGraphQL JWT Authentication
 * fields in Customer type and mutations.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.2.2
 */

namespace WPGraphQL\WooCommerce;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Model\Customer;

/**
 * Class JWT_Auth_Schema_Filters
 */
class JWT_Auth_Schema_Filters {
	/**
	 * Adds filters.
	 *
	 * @return string|null
	 */
	public static function get_auth_class() {
		if ( class_exists( 'WPGraphQL\JWT_Authentication\Auth' ) ) {
			return \WPGraphQL\JWT_Authentication\Auth::class;
		} elseif ( class_exists( 'WPGraphQL\Login\Auth\TokenManager' ) ) {
			return \WPGraphQL\Login\Auth\TokenManager::class;
		} else {
			return null;
		}
	}

	/**
	 * Get the auth token for a user.
	 *
	 * @param \WP_User $user The user object.
	 * 
	 * @throws \GraphQL\Error\UserError If the token cannot be retrieved.
	 *
	 * @return string|null
	 */
	public static function get_auth_token( \WP_User $user ) {
		$auth_class = self::get_auth_class();

		if ( ! $auth_class ) {
			return null;
		}
		/**
		* This method is typed wrong upstream.
		*
		* @var \WP_Error|string|null $token
		*/
		$token = null;
		if ( 'WPGraphQL\JWT_Authentication\Auth' === $auth_class ) {
			$token = $auth_class::get_token( $user );
		} elseif ( 'WPGraphQL\Login\Auth\TokenManager' === $auth_class ) {
			$token = $auth_class::get_auth_token( $user );
		}

		if ( is_wp_error( $token ) ) {
			throw new UserError( $token->get_error_message() );
		}

		return $token;
	}

	/**
	 * Get the refresh token for a user.
	 *
	 * @param \WP_User $user The user object.
	 * 
	 * @throws \GraphQL\Error\UserError If the token cannot be retrieved.
	 *
	 * @return string|null
	 */
	public static function get_refresh_token( \WP_User $user ) {
		$auth_class = self::get_auth_class();

		if ( ! $auth_class ) {
			return null;
		}

		/**
		 * This method is typed wrong upstream.
		 *
		 * @var \WP_Error|string|null $refresh_token
		 */
		$refresh_token = $auth_class::get_refresh_token( $user );

		if ( is_wp_error( $refresh_token ) ) {
			throw new UserError( $refresh_token->get_error_message() );
		}

		return $refresh_token;
	}

	/**
	 * Register filters
	 *
	 * @return void
	 */
	public static function add_filters() {
		// Confirm WPGraphQL JWT Authentication is installed.
		$auth_class = self::get_auth_class();
		if ( ! is_null( $auth_class ) ) {
			add_filter( 'graphql_jwt_user_types', [ self::class, 'add_customer_to_jwt_user_types' ], 10 );
			add_filter( 'graphql_registerCustomerPayload_fields', [ self::class, 'add_jwt_output_fields' ], 10, 3 );
			add_filter( 'graphql_updateCustomerPayload_fields', [ self::class, 'add_jwt_output_fields' ], 10, 3 );
			add_action( 'graphql_register_types', [ self::class, 'add_customer_to_login_payload' ], 10 );
		}
	}

	/**
	 * Adds Customer type to the JWT User type list.
	 *
	 * @param array $types JWT User types.
	 * @return array
	 */
	public static function add_customer_to_jwt_user_types( array $types ) {
		$types[] = 'Customer';

		return $types;
	}

	/**
	 * Adds all JWT related fields to the Customer mutation output.
	 *
	 * @param array                             $fields         Mutation output field definitions.
	 * @param \WPGraphQL\Type\WPInputObjectType $object_type    The WPInputObjectType the fields are be added to.
	 * @param \WPGraphQL\Registry\TypeRegistry  $type_registry  TypeRegistry instance.
	 *
	 * @return array
	 */
	public static function add_jwt_output_fields( $fields, $object_type, $type_registry ): array {
		$fields = array_merge(
			$fields,
			[
				'authToken'    => [
					'type'        => $type_registry->get_type( 'String' ),
					'description' => __( 'JWT Token that can be used in future requests for Authentication', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $payload ) {
						$user = get_user_by( 'ID', $payload['id'] );

						if ( ! $user ) {
							throw new UserError( __( 'User not found.', 'wp-graphql-woocommerce' ) );
						}

						return self::get_auth_token( $user );
					},
				],
				'refreshToken' => [
					'type'        => $type_registry->get_type( 'String' ),
					'description' => __( 'A JWT token that can be used in future requests to get a refreshed jwtAuthToken. If the refresh token used in a request is revoked or otherwise invalid, a valid Auth token will NOT be issued in the response headers.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $payload ) {
						$user = get_user_by( 'ID', $payload['id'] );

						if ( ! $user ) {
							throw new UserError( __( 'User not found.', 'wp-graphql-woocommerce' ) );
						}

						return self::get_refresh_token( $user );
					},
				],
			]
		);

		return $fields;
	}

	/**
	 * Adds "customer" field to "login" mutation payload.
	 *
	 * @return void
	 */
	public static function add_customer_to_login_payload() {
		register_graphql_field(
			'LoginPayload',
			'customer',
			[
				'type'        => 'Customer',
				'description' => __( 'Customer object of authenticated user.', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					$id = $payload['id'];
					return new Customer( $id );
				},
			]
		);

		if ( ! WooCommerce_Filters::is_session_handler_disabled() ) {
			register_graphql_field(
				'LoginPayload',
				'sessionToken',
				[
					'type'        => 'String',
					'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
					'resolve'     => static function () {
						/**
						 * Session Handler.
						 *
						 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
						 */
						$session = \WC()->session;

						return apply_filters( 'graphql_customer_session_token', $session->build_token() );
					},
				]
			);
		}
	}
}
