<?php
/**
 * Handles data for the current customers session.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Utils
 * @since 0.1.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Utils;

/**
 * Class - QL_Session_Handler
 */
class QL_Session_Handler extends WC_Session_Handler {
	/**
	 * Sets the session cookie on-demand (usually after adding an item to the cart).
	 *
	 * Since the cookie name (as of 2.1) is prepended with wp, cache systems like batcache will not cache pages when set.
	 *
	 * Warning: Cookies will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Should the session cookie be set.
	 */
	public function set_customer_session_cookie( $set ) {
		if ( $set ) {
			$to_hash           = $this->_customer_id . '|' . $this->_session_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_customer_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;
			if ( ! isset( $_SERVER[ 'WC_SESSION_' . $this->cookie ] ) || $_SERVER[ 'WC_SESSION_' . $this->cookie ] !== $cookie_value ) {
				add_filter(
					'graphql_response_headers_to_send',
					function( $headers ) {
						$headers['WC_SESSION_TOKEN'] = $cookie_value;
						return $headers;
					}
				);
			}
		}
	}

	/**
	 * Get the session data, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		$cookie_value = isset( $_SERVER[ 'WC_SESSION_' . $this->cookie ] ) ? $_SERVER[ 'WC_SESSION_' . $this->cookie ]  : false; // @codingStandardsIgnoreLine.
		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}
		list( $customer_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $cookie_value );
		if ( empty( $customer_id ) ) {
			return false;
		}
		// Validate hash.
		$to_hash = $customer_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}
		return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
	}

	/**
	 * Forget all session data without destroying it.
	 */
	public function forget_session() {
		add_filter(
			'graphql_response_headers_to_send',
			function( $headers ) {
				$headers[ 'WC_SESSION_' . $this->cookie ] = 'false';
				return $headers;
			}
		);
		wc_empty_cart();
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}
}
