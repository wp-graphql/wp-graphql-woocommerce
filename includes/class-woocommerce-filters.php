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
	 * Initializes hooks for WooCommerce-related utilities.
	 */
	public static function setup() {
		self::$session_header = apply_filters( 'woocommerce_graphql_session_header_name', 'woocommerce-session' );
		if ( ! defined( 'NO_QL_SESSION_HANDLER' ) ) {
			add_action( 'init_graphql_request', array( __CLASS__, 'init_graphql_request' ) );
			add_filter( 'graphql_response_headers_to_send', array( __CLASS__, 'add_session_header_to_expose_headers' ) );
			add_filter( 'graphql_access_control_allow_headers', array( __CLASS__, 'add_session_header_to_allow_headers' ) );
		}
	}

	/**
	 *  Setup QL session handler.
	 */
	public static function init_graphql_request() {
		// Check if request is a GraphQL POST request.
		if ( \WPGraphQL\Router::is_graphql_request() ) {
			add_filter( 'woocommerce_cookie', array( __CLASS__, 'woocommerce_cookie' ) );
			add_filter( 'woocommerce_session_handler', array( __CLASS__, 'init_ql_session_handler' ) );
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
}
