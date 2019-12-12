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
		if ( ! defined( 'NO_QL_SESSION_HANDLER' ) ) {
			add_filter( 'woocommerce_session_handler', array( __CLASS__, 'woocommerce_session_handler' ) );
			add_filter( 'graphql_response_headers_to_send', array( __CLASS__, 'add_session_header_to_expose_headers' ) );
			add_filter( 'graphql_access_control_allow_headers', array( __CLASS__, 'add_session_header_to_allow_headers' ) );
		}
	}

	/**
	 * WooCommerce Session Handler callback
	 *
	 * @param string $session_class  Class name of WooCommerce Session Handler.
	 *
	 * @return string
	 */
	public static function woocommerce_session_handler( $session_class ) {
		if ( \WPGraphQL\Router::is_graphql_request() ) {
			$session_class = '\WPGraphQL\WooCommerce\Utils\QL_Session_Handler';
		}

		return $session_class;
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
}
