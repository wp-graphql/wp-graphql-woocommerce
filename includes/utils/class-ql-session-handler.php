<?php
/**
 * Handles data for the current customers session.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Utils
 * @since 0.1.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Utils;

use WC_Session_Handler;

/**
 * Class - QL_Session_Handler
 */
class QL_Session_Handler extends WC_Session_Handler {
	/**
	 * Encrypt and decrypt
	 *
	 * @author Nazmul Ahsan <n.mukto@gmail.com>
	 * @author Geoff Taylor <kidunot89@gmail.com>
	 * @link http://nazmulahsan.me/simple-two-way-function-encrypt-decrypt-string/
	 *
	 * @param string $string string to be encrypted/decrypted.
	 * @param string $action what to do with this? e for encrypt, d for decrypt.
	 *
	 * @return string
	 */
	private function crypt( $string, $action = 'e' ) {
		// you may change these values to your own.
		$secret_key = apply_filters( 'woographql_session_header_secret_key', 'my_simple_secret_key' );
		$secret_iv  = apply_filters( 'woographql_session_header_secret_iv', 'my_simple_secret_iv' );

		$output         = false;
		$encrypt_method = 'AES-256-CBC';
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( 'e' === $action ) {
			$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		} elseif ( 'd' === $action ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		return $output;
	}

	/**
	 * Returns formatted $_SERVER index from provided string.
	 *
	 * @param string $string String to be formatted.
	 *
	 * @return string
	 */
	private function get_server_key( $string ) {
		return 'HTTP_' . strtoupper( preg_replace( '#[^A-z0-9]#', '_', $string ) );
	}

	/**
	 * Encrypts and sets the session header on-demand (usually after adding an item to the cart).
	 *
	 * Warning: Headers will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Should the session cookie be set.
	 */
	public function set_customer_session_cookie( $set ) {
		$to_hash           = $this->_customer_id . '|' . $this->_session_expiration;
		$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		$cookie_value      = $this->_customer_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
		$this->_has_cookie = true;
		if ( ! isset( $_SERVER[ $this->_cookie ] ) || $_SERVER[ $this->_cookie ] !== $cookie_value ) {
			add_filter(
				'graphql_response_headers_to_send',
				function( $headers ) use ( $cookie_value ) {
					$headers[ $this->_cookie ] = $this->crypt( $cookie_value, 'e' );
					return $headers;
				}
			);
		}
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session() {
		// @codingStandardsIgnoreLine.
		return isset( $_SERVER[ $this->get_server_key( $this->_cookie ) ] ) || $this->_has_cookie || is_user_logged_in();
	}

	/**
	 * Retrieve and decrypt the session data from session, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		// @codingStandardsIgnoreStart.
		$cookie_value = isset( $_SERVER[ $this->get_server_key( $this->_cookie ) ] )
			? $this->crypt( $_SERVER[ $this->get_server_key( $this->_cookie ) ], 'd' )
			: false;
		// @codingStandardsIgnoreEnd.
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
				$headers[ $this->_cookie ] = 'false';
				return $headers;
			}
		);
		wc_empty_cart();
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}
}
