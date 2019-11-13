<?php
/**
 * Adds filters that modify woocommerce functionality on GraphQL requests.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\WooCommerce\Utils\QL_Session_Handler;

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
	 * Register filters
	 */
	public static function add_filters() {
		// Setup QL session handler.
		if ( ! defined( 'NO_QL_SESSION_HANDLER' ) ) {
			// Check if request is a GraphQL POST request.
			self::$session_header = apply_filters( 'woocommerce_graphql_session_header_name', 'woocommerce-session' );
			if ( self::is_graphql_post_request() ) {
				add_filter( 'woocommerce_cookie', array( __CLASS__, 'woocommerce_cookie' ) );
				add_filter( 'woocommerce_session_handler', array( __CLASS__, 'init_ql_session_handler' ) );
			}
			add_filter( 'graphql_response_headers_to_send', array( __CLASS__, 'add_session_header_to_expose_headers' ) );
			add_filter( 'graphql_access_control_allow_headers', array( __CLASS__, 'add_session_header_to_allow_headers' ) );
		}
	}

	/**
	 * Filters WooCommerce cookie key to be used as a HTTP Header on GraphQL HTTP requests
	 *
	 * @param string $cookie WooCommerce cookie key.
	 *
	 * @return string
	 */
	public static function woocommerce_cookie( $cookie ) {
		return self::$session_header;
	}

	/**
	 * Filters WooCommerce session handler class on GraphQL HTTP requests
	 *
	 * @param string $session_class Classname of the current session handler class.
	 *
	 * @return string
	 */
	public static function init_ql_session_handler( $session_class ) {
		return QL_Session_Handler::class;
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
			$headers['Access-Control-Expose-Headers'] = apply_filters( 'woocommerce_cookie', self::$session_header );
		} else {
			$headers['Access-Control-Expose-Headers'] .= ', ' . apply_filters( 'woocommerce_cookie', self::$session_header );
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
	 * Confirm that the current uri is the GraphQL endpoint.
	 *
	 * @return bool
	 */
	private static function is_graphql_post_request() {
		global $wp;
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$haystack = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$needle   = apply_filters( 'graphql_endpoint', 'graphql' );
			$length   = strlen( $needle );
			if ( 0 === $length ) {
				return true;
			}

			return ( substr( $haystack, -$length ) === $needle );
		}

		return false;
	}
}
