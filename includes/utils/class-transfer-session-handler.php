<?php
/**
 * Handles data for the current customers session.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since 0.12.5
 */

namespace WPGraphQL\WooCommerce\Utils;

/**
 * Class Transfer_Session_Handler
 *
 * @property string|int $_customer_id
 *
 * @package WPGraphQL\WooCommerce\Utils
 */
class Transfer_Session_Handler extends \WC_Session_Handler {
	/**
	 * Setup cookie and customer ID.
	 *
	 * @return void
	 */
	public function init_session_cookie() {
		// Retrieve a customer's previous session from DB.
		$this->set_session_expiration();
		$this->_customer_id = $this->generate_customer_id();
		$this->_data        = $this->get_session_data();
	}

	/**
	 * Return true, if valid credential exists
	 *
	 * @return bool
	 */
	protected function verify_auth_request_credentials_exists() {
		$possible_nonces = array_values( Protected_Router::get_nonce_names() );
		// Return false if not nonce names set.
		if ( empty( $possible_nonces ) ) {
			return false;
		}

		// Return false if no matching nonces found in query parameters.
		$query_params = array_keys( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( array_intersect( $possible_nonces, $query_params ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns "session_id" if proper conditions met.
	 *
	 * @return int|string
	 */
	protected function get_posted_session_id() {
		if ( ! $this->verify_auth_request_credentials_exists() ) {
			return 0;
		}
		if ( ! isset( $_REQUEST['session_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return 0;
		}

		return sanitize_text_field( wp_unslash( $_REQUEST['session_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Reads in customer ID from query parameters if specific conditions are met otherwise
	 * a guest ID are generated as usual.
	 *
	 * @return int|string
	 */
	public function generate_customer_id() {
		$session_id = $this->get_posted_session_id();
		if ( 0 !== $session_id ) {
			return $session_id;
		}

		return parent::generate_customer_id();
	}

	/**
	 * Returns client session ID.
	 *
	 * @return string
	 */
	public function get_client_session_id() {
		$session_id = $this->get_posted_session_id();
		/**
		 * Get session data.
		 *
		 * @var null|array{ client_session_id: string, client_session_id_expiration: int } $session_data
		 */
		$session_data = 0 !== $session_id ? $this->get_session( (string) $session_id ) : null;

		if ( ! empty( $session_data ) ) {
			$client_session_id            = ! empty( $session_data['client_session_id'] ) ? $session_data['client_session_id'] : false;
			$client_session_id_expiration = ! empty( $session_data['client_session_id_expiration'] ) ? $session_data['client_session_id_expiration'] : 0;
		} else {
			$client_session_id            = $this->get( 'client_session_id', false );
			$client_session_id_expiration = absint( $this->get( 'client_session_id_expiration', 0 ) );
		}

		if ( false !== $client_session_id && time() < $client_session_id_expiration ) {
			// @phpstan-ignore-next-line
			return $client_session_id;
		}

		return '';
	}
}
