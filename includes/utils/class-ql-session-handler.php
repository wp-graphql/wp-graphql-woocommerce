<?php
/**
 * Handles data for the current customers session.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since 0.1.2
 */

namespace WPGraphQL\WooCommerce\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GraphQL\Error\UserError;
use WC_Session_Handler;

/**
 * Class - QL_Session_Handler
 *
 * @property int $_session_expiring
 * @property int $_session_expiration
 * @property int|string $_customer_id
 */
class QL_Session_Handler extends WC_Session_Handler {

	/**
	 * Stores the name of the HTTP header used to pass the session token.
	 *
	 * @var string $_token
	 */
	protected $_token; // @codingStandardsIgnoreLine

	/**
	 * Stores Timestamp of when the session token was issued.
	 *
	 * @var float $_session_issued
	 */
	protected $_session_issued; // @codingStandardsIgnoreLine

	/**
	 * True when the token exists.
	 *
	 * @var bool $_has_token
	 */
	protected $_has_token = false; // @codingStandardsIgnoreLine

	/**
	 * True when a new session token has been issued.
	 *
	 * @var bool $_issuing_new_token
	 */
	protected $_issuing_new_token = false; // @codingStandardsIgnoreLine

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		$this->_token = apply_filters( 'graphql_woocommerce_cart_session_http_header', 'woocommerce-session' );
		$this->_table = $GLOBALS['wpdb']->prefix . 'woocommerce_sessions';
	}

	/**
	 * Returns formatted $_SERVER index from provided string.
	 *
	 * @param string $header String to be formatted.
	 *
	 * @return string
	 */
	private function get_server_key( $header = null ) {
		/**
		 * Server key.
		 *
		 * @var string $server_key
		 */
		$server_key = preg_replace( '#[^A-z0-9]#', '_', ! empty( $header ) ? $header : $this->_token );
		return null !== $server_key
			? 'HTTP_' . strtoupper( $server_key )
			: '';
	}

	/**
	 * This returns the secret key, using the defined constant if defined, and passing it through a filter to
	 * allow for the config to be able to be set via another method other than a defined constant, such as an
	 * admin UI that allows the key to be updated/changed/revoked at any time without touching server files
	 *
	 * @return mixed|null|string
	 */
	private function get_secret_key() {
		// Use the defined secret key, if it exists.

		$secret_key = defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) && ! empty( GRAPHQL_WOOCOMMERCE_SECRET_KEY )
			? GRAPHQL_WOOCOMMERCE_SECRET_KEY :
			'graphql-woo-cart-session';
		return apply_filters( 'graphql_woocommerce_secret_key', $secret_key );
	}

	/**
	 * Init hooks and session data.
	 *
	 * @return void
	 */
	public function init() {
		$this->init_session_token();
		Session_Transaction_Manager::get( $this );

		/**
		 *  Necessary since Session_Transaction_Manager applies to the reference.
		 *
		 * @var self $this
		 */
		add_action( 'woocommerce_set_cart_cookies', [ $this, 'set_customer_session_token' ], 10 );
		add_action( 'woographql_update_session', [ $this, 'set_customer_session_token' ], 10 );
		add_action( 'graphql_after_resolve_field', [ $this, 'save_if_dirty' ], 10, 4 );
		add_action( 'shutdown', [ $this, 'save_data' ] );
		add_action( 'wp_logout', [ $this, 'destroy_session' ] );

		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', [ $this, 'maybe_update_nonce_user_logged_out' ], 10, 2 );
		}
	}

	/**
	 * Setup token and customer ID.
	 *
	 * @throws UserError Invalid token.
	 *
	 * @return void
	 */
	public function init_session_token() {
		$token = $this->get_session_token();

		// Process existing session if not expired or invalid.
		if ( $token && is_object( $token ) && ! is_wp_error( $token ) ) {
			$this->_customer_id        = $token->data->customer_id;
			$this->_session_issued     = $token->iat;
			$this->_session_expiration = $token->exp;
			$this->_session_expiring   = $token->exp - ( 3600 );
			$this->_has_token          = true;
			$this->_data               = $this->get_session_data();

			// If the user logs in, update session.
			if ( is_user_logged_in() && strval( get_current_user_id() ) !== $this->_customer_id ) {
				$guest_session_id   = $this->_customer_id;
				$this->_customer_id = strval( get_current_user_id() );
				$this->_dirty       = true;

				// If session empty check for previous data associated with customer and assign that to the session.
				if ( empty( $this->_data ) ) {
					$this->_data = $this->get_session_data();
				}

				// @phpstan-ignore-next-line
				$this->save_data( $guest_session_id );
				$this->set_customer_session_token( true );
			}

			// Update session expiration on each action.
			$this->set_session_expiration();
			if ( $token->exp < $this->_session_expiration ) {
				$this->update_session_timestamp( (string) $this->_customer_id, $this->_session_expiration );
			}
		} else {

			// If token invalid throw warning.
			if ( is_wp_error( $token ) ) {
				add_filter(
					'graphql_woocommerce_session_token_errors',
					function( $errors ) use ( $token ) {
						$errors = $token->get_error_message();
						return $errors;
					}
				);
			}

			// Start new session.
			$this->set_session_expiration();

			// Get Customer ID.
			$this->_customer_id = is_user_logged_in() ? get_current_user_id() : $this->generate_customer_id();
			$this->_data        = $this->get_session_data();
			$this->set_customer_session_token( true );
		}//end if
	}

	/**
	 * Retrieve and decrypt the session data from session, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @throws \Exception  Invalid token.
	 * @return false|\WP_Error|object{ iat: int, exp: int, data: object{ customer_id: string } }
	 */
	public function get_session_token() {
		// Get the Auth header.
		$session_header = $this->get_session_header();

		if ( empty( $session_header ) ) {
			return false;
		}

		// Get the token from the header.
		$token_string = sscanf( $session_header, 'Session %s' );
		if ( empty( $token_string ) ) {
			return false;
		}

		list( $token ) = $token_string;

		/**
		 * Try to decode the token
		 */
		try {
			JWT::$leeway = 60;

			$secret = $this->get_secret_key();
			$key    = new Key( $secret, 'HS256' );
			/**
			 * Decode the token
			 *
			 * @var null|object{ iat: int, exp: int, data: object{ customer_id: string }, iss: string } $token
			 */
			$token = ! empty( $token ) ? JWT::decode( $token, $key ) : null;

			// Check if token was successful decoded.
			if ( ! $token ) {
				throw new \Exception( __( 'Failed to decode session token', 'wp-graphql-woocommerce' ) );
			}

			// The Token is decoded now validate the iss.
			if ( empty( $token->iss ) || get_bloginfo( 'url' ) !== $token->iss ) {
				throw new \Exception( __( 'The iss do not match with this server', 'wp-graphql-woocommerce' ) );
			}

			// Validate the customer id in the token.
			if ( empty( $token->data ) || empty( $token->data->customer_id ) ) {
				throw new \Exception( __( 'Customer ID not found in the token', 'wp-graphql-woocommerce' ) );
			}
		} catch ( \Exception $error ) {
			return new \WP_Error( 'invalid_token', $error->getMessage() );
		}//end try

		return $token;
	}

	/**
	 * Get the value of the cart session header from the $_SERVER super global
	 *
	 * @return mixed|string
	 */
	public function get_session_header() {
		$session_header_key = $this->get_server_key();

		// Looking for the cart session header.
		$session_header = isset( $_SERVER[ $session_header_key ] )
			? $_SERVER[ $session_header_key ] //@codingStandardsIgnoreLine
			: false;

		/**
		 * Return the cart session header, passed through a filter
		 *
		 * @param string $session_header  The header used to identify a user's cart session token.
		 */
		return apply_filters( 'graphql_woocommerce_cart_session_header', $session_header );
	}

	/**
	 * Creates JSON Web Token for customer session.
	 *
	 * @return false|string
	 */
	public function build_token() {
		if ( empty( $this->_session_issued ) ) {
			return false;
		}

		/**
		 * Determine the "not before" value for use in the token
		 *
		 * @param float      $issued        The timestamp of token was issued.
		 * @param int|string $customer_id   Customer ID.
		 * @param array      $session_data  Cart session data.
		 */
		$not_before = apply_filters(
			'graphql_woo_cart_session_not_before',
			$this->_session_issued,
			$this->_customer_id,
			$this->_data
		);

		// Configure the token array, which will be encoded.
		$token = [
			'iss'  => get_bloginfo( 'url' ),
			'iat'  => $this->_session_issued,
			'nbf'  => $not_before,
			'exp'  => $this->_session_expiration,
			'data' => [
				'customer_id' => $this->_customer_id,
			],
		];

		/**
		 * Filter the token, allowing for individual systems to configure the token as needed
		 *
		 * @param array      $token         The token array that will be encoded
		 * @param int|string $customer_id   ID of customer associated with token.
		 * @param array      $session_data  Session data associated with token.
		 */
		$token = apply_filters(
			'graphql_woocommerce_cart_session_before_token_sign',
			$token,
			$this->_customer_id,
			$this->_data
		);

		// Encode the token.
		JWT::$leeway = 60;
		$token       = JWT::encode( $token, $this->get_secret_key(), 'HS256' );

		/**
		 * Filter the token before returning it, allowing for individual systems to override what's returned.
		 *
		 * For example, if the user should not be granted a token for whatever reason, a filter could have the token return null.
		 *
		 * @param string     $token         The signed JWT token that will be returned
		 * @param int|string $customer_id   ID of customer associated with token.
		 * @param array      $session_data  Session data associated with token.
		 */
		$token = apply_filters(
			'graphql_woocommerce_cart_session_signed_token',
			$token,
			$this->_customer_id,
			$this->_data
		);

		return $token;
	}

	/**
	 * Sets the session header on-demand (usually after adding an item to the cart).
	 *
	 * Warning: Headers will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Should the session cookie be set.
	 *
	 * @return void
	 */
	public function set_customer_session_token( $set ) {
		if ( ! empty( $this->_session_issued ) && $set ) {
			/**
			 * Set callback session token for use in the HTTP response header and customer/user "sessionToken" field.
			 */
			add_filter(
				'graphql_response_headers_to_send',
				function( $headers ) {
					$token = $this->build_token();
					if ( $token ) {
						$headers[ $this->_token ] = $token;
					}

					return $headers;
				},
				10
			);

			$this->_issuing_new_token = true;
		}
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session() {
		// @codingStandardsIgnoreLine.
		return $this->_issuing_new_token || $this->_has_token || is_user_logged_in();
	}

	/**
	 * Set session expiration.
	 *
	 * @return void
	 */
	public function set_session_expiration() {
		$this->_session_issued = time();
		// 14 Days.
		$this->_session_expiration = apply_filters(
			'graphql_woocommerce_cart_session_expire',
			// Seconds * Minutes * Hours * Days.
			$this->_session_issued + ( 60 * 60 * 24 * 14 )
		);
		// 13 Days.
		$this->_session_expiring = $this->_session_expiration - ( 60 * 60 * 24 );
	}

	/**
	 * Forget all session data without destroying it.
	 *
	 * @return void
	 */
	public function forget_session() {
		if ( isset( $this->_token_to_be_sent ) ) {
			unset( $this->_token_to_be_sent );
		}
		wc_empty_cart();
		$this->_data  = [];
		$this->_dirty = false;

		// Start new session.
		$this->set_session_expiration();

		// Get Customer ID.
		$this->_customer_id = is_user_logged_in() ? get_current_user_id() : $this->generate_customer_id();
	}

	/**
	 * Save any changes to database after a session mutations has been run.
	 *
	 * @param mixed                                $source   Operation root object.
	 * @param array                                $args     Operation arguments.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     Operation ResolveInfo object.
	 *
	 * @return void
	 */
	public function save_if_dirty( $source, $args, $context, $info ) {
		// Bail early, if not one of the session mutations.
		if ( ! in_array( $info->fieldName, Session_Transaction_Manager::get_session_mutations(), true ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return;
		}

		// Update if user recently authenticated.
		if ( is_user_logged_in() && get_current_user_id() !== $this->_customer_id ) {
			$this->_customer_id = get_current_user_id();
			$this->_dirty       = true;
		}

		// Bail if no changes.
		if ( ! $this->_dirty ) {
			return;
		}

		$this->save_data();
	}

	/**
	 * For refreshing session data mid-request when changes occur in concurrent requests.
	 *
	 * @return void
	 */
	public function reload_data() {
		\WC_Cache_Helper::invalidate_cache_group( WC_SESSION_CACHE_GROUP );

		// Get session data.
		$data = $this->get_session( (string) $this->_customer_id );
		if ( is_array( $data ) ) {
			$this->_data = $data;
		}
	}

	/**
	 * Noop for \WC_Session_Handler method.
	 *
	 * Prevents potential crticial errors when calling this method.
	 *
	 * @param bool $set Should the session cookie be set.
	 *
	 * @return void
	 */
	public function set_customer_session_cookie( $set ) {}

	/**
	 * Returns "client_session_id". "client_session_id_expiration" is used
	 * to keep "client_session_id" as fresh as possible.
	 *
	 * For the most strict level of security it's highly recommend these values
	 * be set client-side using the `updateSession` mutation.
	 * "client_session_id" in particular should be salted with some
	 * kind of client identifier like the end-user "IP" or "user-agent"
	 * then hashed parodying the tokens generated by
	 * WP's WP_Session_Tokens class.
	 *
	 * @return string
	 */
	public function get_client_session_id() {
		// Get client session ID.
		$client_session_id            = $this->get( 'client_session_id', false );
		$client_session_id_expiration = absint( $this->get( 'client_session_id_expiration', 0 ) );

		// If client session ID valid return it.
		if ( false !== $client_session_id && time() < $client_session_id_expiration ) {
			// @phpstan-ignore-next-line
			return $client_session_id;
		}

		// Generate a new client session ID.
		$client_session_id            = uniqid();
		$client_session_id_expiration = time() + 3600;
		$this->set( 'client_session_id', $client_session_id );
		$this->set( 'client_session_id_expiration', $client_session_id_expiration );
		$this->save_data();

		// Return new client session ID.
		return $client_session_id;
	}

}
