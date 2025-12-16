<?php
/**
 * Handles data for the current customers session.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since 0.1.2
 */

namespace WPGraphQL\WooCommerce\Utils;

use WC_Session_Handler;
use WPGraphQL\Router;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;

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
	 * True when a new session cookie has been issued.
	 *
	 * @var bool $_issuing_new_cookie
	 */
	protected $_issuing_new_cookie = false; // @codingStandardsIgnoreLine

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		parent::__construct();

		$this->_token = apply_filters( 'graphql_woocommerce_cart_session_http_header', 'woocommerce-session' );
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
		 * Necessary since Session_Transaction_Manager applies to the reference.
		 *
		 * @var self $this
		 */
		if ( Router::is_graphql_http_request() ) {
			add_action( 'woocommerce_set_cart_cookies', [ $this, 'set_customer_session_token' ], 10 );
			add_action( 'woographql_update_session', [ $this, 'set_customer_session_token' ], 10 );
			add_action( 'shutdown', [ $this, 'save_data' ] );
		} else {
			add_action( 'woocommerce_set_cart_cookies', [ $this, 'set_customer_session_cookie' ], 10 );
			add_action( 'shutdown', [ $this, 'save_data' ], 20 );
			add_action( 'wp_logout', [ $this, 'destroy_session' ] );
			if ( ! is_user_logged_in() ) {
				add_filter( 'nonce_user_logged_out', [ $this, 'maybe_update_nonce_user_logged_out' ], 10, 2 );
			}
		}
	}

	/**
	 * Mark the session as dirty.
	 *
	 * To trigger a save of the session data.
	 *
	 * @return void
	 */
	public function mark_dirty() {
		$this->_dirty = true;
	}

	/**
	 * Setup token and customer ID.
	 *
	 * @throws \GraphQL\Error\UserError Invalid token.
	 *
	 * @return void
	 */
	public function init_session_token() {
		// If user is logged in via JWT, set customer_id immediately to ensure correct cart is loaded
		if ( is_user_logged_in() ) {
			$this->_customer_id = strval( get_current_user_id() );
		}

		/**
		 * @var object{ iat: int, exp: int, data: object{ customer_id: string } }|false|\WP_Error $token
		 */
		$token = $this->get_session_token();

		// Process existing session if not expired or invalid.
		if ( $token && is_object( $token ) && ! is_wp_error( $token ) ) {
			// If user is logged in, always use user ID as customer_id (not guest session from token)
			if ( is_user_logged_in() ) {
				$guest_session_id = $token->data->customer_id;
				
				// Get guest cart data BEFORE switching to user session
				$guest_data = $this->get_session( (string) $guest_session_id );
				if ( ! is_array( $guest_data ) ) {
					$guest_data = [];
				}
				
				// Now switch to user session
				$this->_customer_id = strval( get_current_user_id() );
				$this->_dirty       = true;
				
				// Get user's cart data
				$this->_data = $this->get_session_data();

				// If user's cart is empty but guest cart has items, merge them
				if ( empty( $this->_data ) && ! empty( $guest_data ) ) {
					$this->_data = $guest_data;
				}

				// Save guest session data to user session
				if ( ! empty( $guest_session_id ) && strval( $guest_session_id ) !== $this->_customer_id ) {
					$this->save_data( $guest_session_id );
				}
				
				// Ensure WooCommerce cart is loaded from session after setting customer_id
				if ( function_exists( 'WC' ) && WC()->cart ) {
					WC()->cart->get_cart_from_session();
				}
			} else {
				// Guest user - use customer_id from token
				$this->_customer_id        = $token->data->customer_id;
				$this->_data               = $this->get_session_data();
				
				// Ensure WooCommerce cart is loaded from session after setting customer_id
				if ( function_exists( 'WC' ) && WC()->cart ) {
					WC()->cart->get_cart_from_session();
				}
			}

			$this->_session_issued     = $token->iat;
			$this->_session_expiration = $token->exp;
			$this->_session_expiring   = $token->exp - ( 3600 );
			$this->_has_token          = true;

			// Update session expiration on each action.
			$this->set_session_expiration();
			if ( $token->exp < $this->_session_expiration ) {
				$this->update_session_timestamp( (string) $this->_customer_id, $this->_session_expiration );
			}

			// Set session token for logged in users
			if ( is_user_logged_in() && Router::is_graphql_http_request() ) {
				$this->set_customer_session_token( true );
			}
		} elseif ( is_wp_error( $token ) ) {
			// If user is logged in via JWT, don't throw error for invalid/missing WooCommerce session token
			// Just create a new session for them
			if ( ! is_user_logged_in() ) {
				// Guest user - log error but don't block (for guest cart support)
				add_filter(
					'graphql_woocommerce_session_token_errors',
					static function ( $errors ) use ( $token ) {
						// Return null to allow continuation without throwing error
						graphql_debug( $token->get_error_message(), [ 'type' => 'SESSION_TOKEN_WARNING' ] );
						return null;
					}
				);
			}
			// If user is logged in, we'll create a new session below, so no need to set error filter
		}

		$start_new_session = ! $token || is_wp_error( $token );
		if ( ! $start_new_session ) {
			return;
		}

		// Distribute new session token on GraphQL requests, otherwise distribute a new session cookie.
		if ( Router::is_graphql_http_request() ) {
			// Start new session.
			$this->set_session_expiration();

			// Get Customer ID - use logged in user ID if available, otherwise generate guest ID
			if ( ! isset( $this->_customer_id ) || empty( $this->_customer_id ) ) {
				$this->_customer_id = is_user_logged_in() ? get_current_user_id() : $this->generate_customer_id();
			}
			
			// Load session data for the customer
			$this->_data = $this->get_session_data();
			
			// Ensure WooCommerce cart is loaded from session after setting customer_id
			if ( ! empty( $this->_customer_id ) && function_exists( 'WC' ) && WC()->cart ) {
				WC()->cart->get_cart_from_session();
			}
			
			$this->set_customer_session_token( true );
		} else {
			$this->init_session_cookie();
		}
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
		} catch ( \Throwable $error ) {
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
	 * Determine if a JWT is being sent in the page response.
	 *
	 * @return bool
	 */
	public function sending_token() {
		return $this->_has_token || $this->_issuing_new_token;
	}

	/**
	 * Determine if a HTTP cookie is being sent in the page response.
	 *
	 * @return bool
	 */
	public function sending_cookie() {
		return $this->_has_cookie || $this->_issuing_new_cookie;
	}

	/**
	 * Creates JSON Web Token for customer session.
	 *
	 * @return false|string
	 */
	public function build_token() {
		if ( empty( $this->_session_issued ) || ! $this->sending_token() ) {
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
				function ( $headers ) {
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
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function set_customer_session_cookie( $set ) {
		parent::set_customer_session_cookie( $set );

		if ( $set ) {
			$this->_issuing_new_cookie = true;
		}
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session() {

		// @codingStandardsIgnoreLine.
		return $this->_issuing_new_token || $this->_has_token || parent::has_session();
	}

	/**
	 * Set session expiration.
	 *
	 * @return void
	 */
	public function set_session_expiration() {
		$this->_session_issued = time();
		// 47 hours.
		$this->_session_expiring = apply_filters( 'wc_session_expiring', $this->_session_issued + ( 60 * 60 * 47 ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		// 48 hours.
		$this->_session_expiration = apply_filters( 'wc_session_expiration', $this->_session_issued + ( 60 * 60 * 48 ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$this->_session_expiration = apply_filters_deprecated(
			'graphql_woocommerce_cart_session_expire',
			[ $this->_session_expiration ],
			'0.21.0',
			'wc_session_expiration'
		);
	}

	/**
	 * Save any changes to database after a session mutations has been run.
	 *
	 * @return void
	 */
	public function save_if_dirty() {
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
		
		// Ensure WooCommerce cart is loaded from session after reloading session data
		// This is critical to ensure cart contains all items from session, not just the current request
		if ( function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->get_cart_from_session();
		}
	}

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
