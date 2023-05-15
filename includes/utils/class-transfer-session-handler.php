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
 */
class Transfer_Session_Handler extends \WC_Session_Handler {
	/**
	 * Reads in customer ID from query parameters if specific conditions are met otherwise
     * a guest ID are generated as usual.
	 * @return string
	 */
	public function generate_customer_id() {
        $possible_nonces = array_values( Protected_Router::get_nonce_names() );
        // Bail if not nonce names set.
        if ( empty( $possible_nonces ) ) {
            return parent::generate_customer_id();
        }

        // Bail if no matching nonces found in query parameters.
        $query_params = array_keys( $_REQUEST );
        if ( empty( array_intersect( $possible_nonces, $query_params ) ) ) {
            return parent::generate_customer_id();
        }

        // Bail if no session ID sent as a query param.
        if ( ! isset( $_REQUEST['session_id'] ) ) {
            return parent::generate_customer_id();
        }

        $session_id = sanitize_text_field( wp_unslash( $_REQUEST['session_id'] ) );
        return $session_id;
	}
}
