<?php
/**
 * Adds filters that modify woocommerce functionality on GraphQL requests.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce;

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
	 */
	public static function setup() {
		self::$session_header = apply_filters( 'graphql_woo_cart_session_http_header', 'woocommerce-session' );
		// Check if request is a GraphQL POST request.
		if ( ! defined( 'NO_QL_SESSION_HANDLER' ) && self::is_graphql_request() ) {
			// Set session handler.
			add_filter(
				'woocommerce_session_handler',
				function() {
					return '\WPGraphQL\WooCommerce\Utils\QL_Session_Handler';
				}
			);

			add_filter( 'graphql_response_headers_to_send', array( __CLASS__, 'add_session_header_to_expose_headers' ) );
			add_filter( 'graphql_access_control_allow_headers', array( __CLASS__, 'add_session_header_to_allow_headers' ) );
		}
	}

	/**
	 * Append session header to the exposed headers in GraphQL responses
	 *
	 * @param array $headers GraphQL responser headers.
	 *
	 * @return array
	 */
	public static function add_session_header_to_expose_headers( $headers ) {
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
	 *
	 * @return array
	 */
	public static function add_session_header_to_allow_headers( array $allowed_headers ) {
		$allowed_headers[] = self::$session_header;
		return $allowed_headers;
	}

	/**
	 * Confirm that the current request is being made to the GraphQL endpoint.
	 *
	 * @return bool
	 */
	private static function is_graphql_request() {
		// If before 'init' check $_SERVER.
		if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$haystack = esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) )
				. esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$needle   = \home_url( \WPGraphQL\Router::$route );
			$len      = strlen( $needle );
			return ( substr( $haystack, 0, $len ) === $needle );
		}

		return false;
	}
}
