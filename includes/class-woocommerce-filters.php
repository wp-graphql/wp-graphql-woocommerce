<?php
/**
 * Adds filters that modify woocommerce functionality on GraphQL requests.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;

/**
 * Class WooCommerce_Filters
 */
class WooCommerce_Filters {

	/**
	 * Stores instance session header name.
	 *
	 * @var string
	 */
	private static $session_header;

	/**
	 * Initializes hooks for WooCommerce-related utilities.
	 *
	 * @return void
	 */
	public static function setup() {
		self::$session_header = apply_filters( 'graphql_woocommerce_cart_session_http_header', 'woocommerce-session' );

		// Check if request is a GraphQL POST request.
		if ( ! self::is_session_handler_disabled() ) {
			add_filter( 'woocommerce_session_handler', [ __CLASS__, 'woocommerce_session_handler' ] );
			add_filter( 'graphql_response_headers_to_send', [ __CLASS__, 'add_session_header_to_expose_headers' ] );
			add_filter( 'graphql_access_control_allow_headers', [ __CLASS__, 'add_session_header_to_allow_headers' ] );
		}

		// Add better support for Stripe payment gateway.
		add_filter( 'graphql_stripe_process_payment_args', [ __CLASS__, 'woographql_stripe_gateway_args' ], 10, 2 );
	}

	/**
	 * Returns true if the "Disable QL Session Handler" option is checked on the settings page.
	 *
	 * @return boolean
	 */
	public static function is_session_handler_disabled() {
		return defined( 'NO_QL_SESSION_HANDLER' ) || 'on' === woographql_setting( 'disable_ql_session_handler', 'off' );
	}

	/**
	 * Returns array of enabled authorizing URL field slugs.
	 *
	 * @return array
	 */
	public static function enabled_authorizing_url_fields() {
		if ( defined( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' ) ) {
			return apply_filters(
				'woographql_enabled_authorizing_url_fields',
				[
					'cart_url'               => 'cart_url',
					'checkout_url'           => 'checkout_url',
					'add_payment_method_url' => 'add_payment_method_url',
				]
			);
		}
		return woographql_setting( 'enable_authorizing_url_fields', [] );
	}

	/**
	 * Return the nonce query parameter name for the provided field.
	 *
	 * @param string $field  URL field slug.
	 *
	 * @return string null
	 */
	public static function get_authorizing_url_nonce_param_name( $field ) {
		$flag_name      = strtoupper( $field );
		$hardcoded_name = defined( "{$flag_name}_NONCE_PARAM" ) ? constant( "{$flag_name}_NONCE_PARAM" ) : false;
		if ( ! empty( $hardcoded_name ) ) {
			return $hardcoded_name;
		}

		return woographql_setting( "{$field}_nonce_param", null );
	}

	/**
	 * WooCommerce Session Handler callback
	 *
	 * @param string $session_class  Class name of WooCommerce Session Handler.
	 * @return string
	 */
	public static function woocommerce_session_handler( $session_class ) {
		if ( \WPGraphQL\Router::is_graphql_http_request() ) {
			$session_class = '\WPGraphQL\WooCommerce\Utils\QL_Session_Handler';
		} elseif ( WooGraphQL::auth_router_is_enabled() ) {
			require_once get_includes_directory() . 'utils/class-protected-router.php';
			require_once get_includes_directory() . 'utils/class-transfer-session-handler.php';

			$session_class = Utils\Protected_Router::is_auth_request() ? '\WPGraphQL\WooCommerce\Utils\Transfer_Session_Handler' : $session_class;
		}

		return $session_class;
	}

	/**
	 * Append session header to the exposed headers in GraphQL responses
	 *
	 * @param array $headers GraphQL responser headers.
	 * @return array
	 */
	public static function add_session_header_to_expose_headers( array $headers ) {
		if ( empty( $headers['Access-Control-Expose-Headers'] ) ) {
			$headers['Access-Control-Expose-Headers'] = self::$session_header;
		} else {
			$headers['Access-Control-Expose-Headers'] .= ', ' . self::$session_header;
		}

		return $headers;
	}

	/**
	 * Append the session header to the allowed headers in GraphQL responses
	 *
	 * @param array $allowed_headers The existing allowed headers.
	 * @return array
	 */
	public static function add_session_header_to_allow_headers( array $allowed_headers ) {
		$allowed_headers[] = self::$session_header;
		return $allowed_headers;
	}

	/**
	 * Adds extra arguments to the Stripe Gateway process payment call.
	 *
	 * @param array  $gateway_args    Arguments to be passed to the gateway `process_payment` method.
	 * @param string $payment_method  Payment gateway ID.
	 *
	 * @return array
	 */
	public static function woographql_stripe_gateway_args( $gateway_args, $payment_method ) {
		if ( 'stripe' === $payment_method ) {
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
}
