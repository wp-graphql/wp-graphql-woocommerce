<?php
/**
 * Compatibility integrations with third-party plugins.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Model\Customer;

/**
 * Class Compatibility
 */
class Compatibility {
	/**
	 * Register all compatibility filters.
	 *
	 * @return void
	 */
	public static function setup() {
		self::register_wc_admin_settings();
		self::add_acf_filters();
		self::add_jwt_auth_filters();
		self::add_stripe_gateway_filters();
		self::add_swp_filters();
	}

	/**
	 * Register WC admin settings for GraphQL requests.
	 *
	 * WooCommerce only registers settings groups during rest_api_init.
	 * We need them available for GraphQL settings queries and mutations.
	 *
	 * @return void
	 */
	private static function register_wc_admin_settings() {
		if ( method_exists( WC(), 'register_wp_admin_settings' ) ) {
			WC()->register_wp_admin_settings(); // @phpstan-ignore method.private (public since WC 9.0)
		}
	}

	/**
	 * Register WPGraphQL ACF compatibility filters.
	 *
	 * @return void
	 */
	private static function add_acf_filters() {
		add_filter( 'graphql_acf_get_root_id', [ self::class, 'resolve_crud_root_id' ], 10, 2 );
		add_filter( 'graphql_acf_post_object_source', [ self::class, 'resolve_post_object_source' ], 10, 2 );
	}

	/**
	 * Resolve post object ID from CRUD object Model.
	 *
	 * @param integer|null $id    Post object database ID.
	 * @param mixed        $root  Root resolver.
	 *
	 * @return integer|null
	 */
	public static function resolve_crud_root_id( $id, $root ) {
		if ( $root instanceof Model\WC_Post ) {
			$id = absint( $root->ID );
		}

		return $id;
	}

	/**
	 * Filters ACF "post_object" field type resolver to ensure that
	 * the proper Type source is provided for WooCommerce CPTs.
	 *
	 * @param mixed|null $source  source of the data being provided.
	 * @param mixed|null $value  Post ID.
	 *
	 * @return mixed|null
	 */
	public static function resolve_post_object_source( $source, $value ) {
		$post = get_post( $value );
		if ( $post instanceof \WP_Post ) {
			switch ( $post->post_type ) {
				case 'shop_coupon':
					$source = new Model\Coupon( $post->ID );
					break;
				case 'shop_order':
					$source = new Model\Order( $post->ID );
					break;
				case 'product':
					$source = new Model\Product( $post->ID );
					break;
				case 'product_variation':
					$source = new Model\Product_Variation( $post->ID );
					break;
			}
		}

		return $source;
	}

	/**
	 * Get the JWT auth class if available.
	 *
	 * @return string|null
	 */
	public static function get_auth_class() {
		if ( class_exists( 'WPGraphQL\JWT_Authentication\Auth' ) ) {
			return \WPGraphQL\JWT_Authentication\Auth::class;
		} elseif ( class_exists( 'WPGraphQL\Login\Auth\TokenManager' ) ) {
			return \WPGraphQL\Login\Auth\TokenManager::class;
		}

		return null;
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
		 * @var \WP_Error|string|null $refresh_token
		 */
		$refresh_token = $auth_class::get_refresh_token( $user );

		if ( is_wp_error( $refresh_token ) ) {
			throw new UserError( $refresh_token->get_error_message() );
		}

		return $refresh_token;
	}

	/**
	 * Register WPGraphQL JWT Authentication compatibility filters.
	 *
	 * @return void
	 */
	private static function add_jwt_auth_filters() {
		$auth_class = self::get_auth_class();
		if ( is_null( $auth_class ) ) {
			return;
		}

		add_filter( 'graphql_jwt_user_types', [ self::class, 'add_customer_to_jwt_user_types' ], 10 );
		add_filter( 'graphql_registerCustomerPayload_fields', [ self::class, 'add_jwt_output_fields' ], 10, 3 );
		add_filter( 'graphql_updateCustomerPayload_fields', [ self::class, 'add_jwt_output_fields' ], 10, 3 );
		add_action( 'graphql_register_types', [ self::class, 'add_customer_to_login_payload' ], 10 );
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
		return array_merge(
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

		if ( ! wc_graphql_is_session_handler_disabled() ) {
			$token_type = woographql_setting( 'set_session_token_type', 'legacy' );
			if ( in_array( $token_type, [ 'legacy', 'both' ], true ) ) {
				register_graphql_field(
					'LoginPayload',
					'sessionToken',
					[
						'type'        => 'String',
						'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
						'resolve'     => static function () {
							/** @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session */
							$session = \WC()->session;

							return apply_filters( 'graphql_customer_session_token', $session->build_token() );
						},
					]
				);
			}
			if ( in_array( $token_type, [ 'store-api', 'both' ], true ) ) {
				register_graphql_field(
					'LoginPayload',
					'cartToken',
					[
						'type'        => 'String',
						'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
						'resolve'     => static function () {
							/** @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session */
							$session = \WC()->session;

							return apply_filters( 'graphql_customer_session_token', $session->build_cart_token() );
						},
					]
				);
			}
		}
	}

	/**
	 * Register WooCommerce Stripe Gateway compatibility filters.
	 *
	 * @return void
	 */
	private static function add_stripe_gateway_filters() {
		add_filter( 'graphql_stripe_process_payment_args', [ self::class, 'woocommerce_gateway_stripe_args' ], 10, 2 );
	}

	/**
	 * Adds extra arguments to the Stripe Gateway process payment call.
	 *
	 * @param array  $gateway_args    Arguments to be passed to the gateway `process_payment` method.
	 * @param string $payment_method  Payment gateway ID.
	 *
	 * @return array
	 */
	public static function woocommerce_gateway_stripe_args( $gateway_args, $payment_method ) {
		/** @var false|\WC_Order|\WC_Order_Refund $order */
		$order = wc_get_order( $gateway_args[0] );
		if ( false === $order ) {
			return $gateway_args;
		}

		$stripe_source_id = $order->get_meta( '_stripe_source_id' );
		if ( 'stripe' === $payment_method && ! empty( $stripe_source_id ) ) {
			$gateway_args = [
				$gateway_args[0],
				true,
				false,
				false,
				true,
			];
		}

		return $gateway_args;
	}

	/**
	 * Register SearchWP/QL Search compatibility filters.
	 *
	 * @return void
	 */
	private static function add_swp_filters() {
		add_filter( 'graphql_swp_result_possible_types', [ self::class, 'searchwp_result_possible_types' ] );
	}

	/**
	 * Adds product types to QL Search SWPResult possible types.
	 *
	 * @param array $type_names SWPResults possible types.
	 *
	 * @return array
	 */
	public static function searchwp_result_possible_types( array $type_names ) {
		if ( in_array( 'Product', $type_names, true ) ) {
			$type_names = array_merge(
				array_filter(
					$type_names,
					static function ( $type_name ) {
						return 'Product' !== $type_name;
					}
				),
				[
					'SimpleProduct',
					'VariableProduct',
					'GroupProduct',
					'ExternalProduct',
				]
			);
		}

		return $type_names;
	}
}
