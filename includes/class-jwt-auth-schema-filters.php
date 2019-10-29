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
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\User;

/**
 * Class JWT_Auth_Schema_Filters
 */
class JWT_Auth_Schema_Filters {
	/**
	 * Register filters
	 */
	public static function add_filters() {
		// Confirm WPGraphQL JWT Authentication is install and activated.
		if ( defined( 'WPGRAPHQL_JWT_AUTHENTICATION_VERSION' ) ) {
			add_filter( 'graphql_customer_fields', array( __CLASS__, 'add_jwt_token_fields' ), 10 );
			add_filter( 'graphql_registerCustomerPayload_fields', array( __CLASS__, 'add_jwt_output_fields' ), 10, 1 );
			add_filter( 'graphql_updateCustomerPayload_fields', array( __CLASS__, 'add_jwt_output_fields' ), 10, 1 );
		}
	}

	/**
	 * Adds all JWT related fields to the Customer type.
	 *
	 * @param array $fields  Customer type field definitions.
	 */
	public static function add_jwt_token_fields( $fields ) {
		$jwt_token_fields = array();

		// Wrapper field resolvers in a lambda that retrieves the WP_User object for the corresponding customer.
		foreach ( \WPGraphQL\JWT_Authentication\ManageTokens::add_user_fields( array() ) as $field_name => $field ) {
			$root_resolver                   = $field['resolve'];
			$jwt_token_fields[ $field_name ] = array_merge(
				$field,
				array(
					'resolve' => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $root_resolver ) {
						$wp_user = get_user_by( 'id', $source->ID );
						if ( $wp_user ) {
							$user = new User( $wp_user );
							return $root_resolver( $user, $args, $context, $info );
						}

						return null;
					},
				)
			);
		}

		$fields = array_merge( $fields, $jwt_token_fields );

		return $fields;
	}

	/**
	 * Adds all JWT related fields to the Customer mutation output.
	 *
	 * @param array $fields  mutation output field definitions.
	 */
	public static function add_jwt_output_fields( $fields ) {
		$fields = array_merge(
			$fields,
			array(
				'authToken'    => array(
					'type'        => \WPGraphQL\Types::string(),
					'description' => __( 'JWT Token that can be used in future requests for Authentication', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $payload ) {
						$user = get_user_by( 'ID', $payload['id'] );
						$token = \WPGraphQL\JWT_Authentication\Auth::get_token( $user );

						if ( is_wp_error( $token ) ) {
							throw new UserError( $token->get_error_message() );
						}

						return $token;
					},
				),
				'refreshToken' => array(
					'type'        => \WPGraphQL\Types::string(),
					'description' => __( 'A JWT token that can be used in future requests to get a refreshed jwtAuthToken. If the refresh token used in a request is revoked or otherwise invalid, a valid Auth token will NOT be issued in the response headers.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $payload ) {
						$user = get_user_by( 'ID', $payload['id'] );
						$refresh_token = \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user );

						if ( is_wp_error( $refresh_token ) ) {
							throw new UserError( $refresh_token->get_error_message() );
						}

						return $refresh_token;
					},
				),
			)
		);

		return $fields;
	}
}
