<?php
/**
 * Sets up the auth endpoint
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since   0.12.5
 */

namespace WPGraphQL\WooCommerce\Utils;

use WPGraphQL\WooCommerce\WooCommerce_Filters;

/**
 * Class Protected_Router
 */
class Protected_Router {

	/**
	 * Stores the instance of the Protected_Router class
	 *
	 * @var null|Protected_Router
	 */
	private static $instance = null;

	/**
	 * The default route
	 *
	 * @var string
	 */
	public static $default_route = 'transfer-session';

	/**
	 * Sets the route to use as the endpoint
	 *
	 * @var string
	 */
	public static $route = null;

	/**
	 * Set the default status code to 200.
	 *
	 * @var int
	 */
	public static $http_status_code = 200;

	/**
	 * Protected_Router constructor
	 */
	private function __construct() {
		self::$route = woographql_setting( 'authorizing_url_endpoint', apply_filters( 'woographql_authorizing_url_endpoint', self::$default_route ) );
		/**
		 * Create the rewrite rule for the route
		 */
		add_action( 'init', [ $this, 'add_rewrite_rule' ], 10 );

		/**
		 * Add the query var for the route
		 */
		add_filter( 'query_vars', [ $this, 'add_query_var' ], 1, 1 );

		/**
		 * Redirects the route to the graphql processor
		 */
		add_action( 'pre_get_posts', [ $this, 'resolve_request' ], 1 );
	}

	/**
	 * Returns the Protected_Router singleton instance.
	 *
	 * @return Protected_Router
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		// Return the Protected_Router Instance.
		return self::$instance;
	}

	/**
	 * Initializes the Protected_Router singleton.
	 *
	 * @return void
	 */
	public static function initialize() {
		self::instance();
	}

	/**
	 * Throw error on object clone.
	 * The whole idea of the singleton design pattern is that there is a single object
	 * therefore, we don't want the object to be cloned.
	 *
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Protected_Router class should not be cloned.', 'wp-graphql-woocommerce' ), esc_html( WPGRAPHQL_WOOCOMMERCE_VERSION ) );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @return void
	 */
	public function __wakeup() {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the Protected_Router class is not allowed', 'wp-graphql-woocommerce' ), esc_html( WPGRAPHQL_WOOCOMMERCE_VERSION ) );
	}

	/**
	 * Adds rewrite rule for the route endpoint
	 *
	 * @return void
	 */
	public function add_rewrite_rule() {
		add_rewrite_rule(
			self::$route . '/?$',
			'index.php?' . self::$route . '=true',
			'top'
		);
	}

	/**
	 * Adds the query_var for the route
	 *
	 * @param array $query_vars The array of whitelisted query variables.
	 *
	 * @return array
	 */
	public function add_query_var( $query_vars ) {
		$query_vars[] = self::$route;

		return $query_vars;
	}

	/**
	 * Returns true when the current request is a request to download the plugin.
	 *
	 * @return boolean
	 */
	public static function is_auth_request() {
		$is_auth_request = false;
		if ( isset( $_GET[ self::$route ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$is_auth_request = true;
		} else {
			// Check the server to determine if the auth endpoint is being requested.
			if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
				$host = wp_unslash( $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$uri  = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				if ( ! is_string( $host ) ) {
					return false;
				}

				if ( ! is_string( $uri ) ) {
					return false;
				}

				$parsed_site_url    = wp_parse_url( site_url( self::$route ), PHP_URL_PATH );
				$auth_url           = ! empty( $parsed_site_url ) ? wp_unslash( $parsed_site_url ) : self::$route;
				$parsed_request_url = wp_parse_url( $uri, PHP_URL_PATH );
				$request_url        = ! empty( $parsed_request_url ) ? wp_unslash( $parsed_request_url ) : '';

				// Determine if the route is indeed a download request.
				$is_auth_request = false !== strpos( $request_url, $auth_url );
			}//end if
		}//end if

		/**
		 * Filter whether the request is a download request. Default is false.
		 *
		 * @param boolean $is_download_request Whether the request is a request to download the plugin. Default false.
		 */
		return apply_filters( 'woographql_is_auth_request', $is_auth_request );
	}

	/**
	 * This resolves the http request and ensures that WordPress can respond with the appropriate
	 * response instead of responding with a template from the standard WordPress Template
	 * Loading process
	 *
	 * @return void
	 */
	public function resolve_request() {

		/**
		 * Access the $wp_query object
		 */
		global $wp_query;

		/**
		 * Ensure we're on the registered route for graphql route
		 */
		if ( ! $this->is_auth_request() ) {
			return;
		}

		/**
		 * Set is_home to false
		 */
		$wp_query->is_home = false;

		/**
		 * Process the GraphQL query Request
		 */
		$this->process_auth_request();
	}

	/**
	 * Returns the name of all the valid nonce names.
	 *
	 * @return array
	 */
	public static function get_nonce_names() {
		$enabled_authorizing_url_fields = WooCommerce_Filters::enabled_authorizing_url_fields();
		if ( empty( $enabled_authorizing_url_fields ) ) {
			return [];
		}
		$nonce_names = [];
		foreach ( array_keys( $enabled_authorizing_url_fields ) as $field ) {
			$nonce_names[ $field ] = WooCommerce_Filters::get_authorizing_url_nonce_param_name( $field );
		}
		return array_filter( $nonce_names );
	}

	/**
	 * Returns the nonce action prefix for the provided field.
	 *
	 * @param string $field  Field.
	 * @return string|null
	 */
	public function get_nonce_prefix( $field ) {
		switch ( $field ) {
			case 'cart_url':
				return 'load-cart_';
			case 'checkout_url':
				return 'load-checkout_';
			case 'add_payment_method_url':
				return 'load-account_';
			default:
				return apply_filters( 'woographql_auth_nonce_prefix', null, $field, $this );
		}
	}

	/**
	 * Returns the target endpoint url for the provided field.
	 *
	 * @param string $field  Field.
	 * @return string|null
	 */
	public function get_target_endpoint( $field ) {
		switch ( $field ) {
			case 'cart_url':
				return wc_get_endpoint_url( 'cart' );
			case 'checkout_url':
				return wc_get_endpoint_url( 'checkout' );
			case 'add_payment_method_url':
				return wc_get_account_endpoint_url( 'add-payment-method' );
			default:
				return apply_filters( 'woographql_auth_target_endpoint', null, $field, $this );
		}
	}

	/**
	 * Redirects to homepage.
	 *
	 * @return void
	 */
	private function redirect_to_home() {
		status_header( 404 );
		wp_safe_redirect( home_url() );
		exit;
	}

	/**
	 * Send stable version of plugin to download.
	 *
	 * @throws \Exception Session not found.
	 *
	 * @return void
	 */
	private function process_auth_request() {
		// Bail early if session ID or nonce not found.
		$nonce_names = $this->get_nonce_names();
		if ( empty( $nonce_names ) ) {
			$this->redirect_to_home();
			return;
		}

		/**
		 * Nonce prefix
		 *
		 * @var string $nonce_prefix
		 */
		$nonce_prefix = null;

		/**
		 * Session ID
		 *
		 * @var string $session_id
		 */
		$session_id = null;

		/**
		 * Nonce
		 *
		 * @var string $nonce
		 */
		$nonce = null;

		/**
		 * Field
		 *
		 * @var string $field
		 */
		$field = null;
		foreach ( $nonce_names as $possible_field => $nonce_param ) {
			if ( in_array( $nonce_param, array_keys( $_REQUEST ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$field        = $possible_field;
				$nonce_prefix = $this->get_nonce_prefix( $field );
				$session_id   = isset( $_REQUEST['session_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['session_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$nonce        = isset( $_REQUEST[ $nonce_param ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_param ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				break;
			}
		}

		if ( empty( $field ) || empty( $nonce_prefix ) || empty( $session_id ) || empty( $nonce ) ) {
			$this->redirect_to_home();
			return;
		}

		// Bail early if session user already authenticated.
		if ( 0 !== get_current_user_id() && get_current_user_id() === absint( $session_id ) ) {
			$redirect_url = $this->get_target_endpoint( (string) $field );
			if ( empty( $redirect_url ) ) {
				$this->redirect_to_home();
				return;
			}
			wp_safe_redirect( $redirect_url );
			exit;
		}

		// Unauthenticate if current user not session user.
		if ( 0 !== get_current_user_id() ) {
			wp_clear_auth_cookie();
			wp_set_current_user( 0 );
		}

		// Verify nonce.
		if ( null !== $nonce && ! woographql_verify_nonce( $nonce, $nonce_prefix . $session_id ) ) {
			$this->redirect_to_home();
		}

		// If Session ID is a user ID authenticate as session user.
		if ( 0 !== absint( $session_id ) ) {
			$user_id = absint( $session_id );
			wp_clear_auth_cookie();
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
		}

		/**
		 * Session object
		 *
		 * @var Transfer_Session_Handler $session
		 */
		$session = \WC()->session;

		// Read session data connected to session ID.
		$session_data = $session->get_session( $session_id );

		// We were passed a session ID, yet no session was found. Let's log this and bail.
		if ( ! is_array( $session_data ) || empty( $session_data ) ) {
			// TODO: Switch to WC Notices.
			throw new \Exception( 'Could not locate WooCommerce session on checkout' );
		}

		// Reinitialize session and save session cookie before redirect.
		$session->init_session_cookie();

		// Set the session variable.
		foreach ( $session_data as $key => $value ) {
			$session->set( $key, maybe_unserialize( $value ) );
		}
		$session->set_customer_session_cookie( true );

		// After session has been restored on redirect to destination.
		$redirect_url = $this->get_target_endpoint( (string) $field );
		if ( empty( $redirect_url ) ) {
			$this->redirect_to_home();
			return;
		}
		wp_safe_redirect( $redirect_url );
		exit;
	}
}

