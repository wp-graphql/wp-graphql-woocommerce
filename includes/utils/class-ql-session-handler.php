<?php
/**
 * Handles data for the current customers session.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since 0.1.2
 */

namespace WPGraphQL\WooCommerce\Utils;

use Firebase\JWT\JWT;
use GraphQL\Error\UserError;
use WC_Session_Handler;

/**
 * Class - QL_Session_Handler
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
	 * @var string $_session_issued
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
		return ! empty( $header )
			? 'HTTP_' . strtoupper( preg_replace( '#[^A-z0-9]#', '_', $header ) )
			: 'HTTP_' . strtoupper( preg_replace( '#[^A-z0-9]#', '_', $this->_token ) );
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
	 */
	public function init() {
		$this->init_session_token();

		add_action( 'woocommerce_set_cart_cookies', array( $this, 'set_customer_session_token' ), 10 );
		add_action( 'shutdown', array( $this, 'save_data' ) );

		add_action( 'graphql_before_resolve_field', array( $this, 'update_transaction_queue' ), 10, 8 );
		add_action( 'shutdown', array( $this, 'pop_transaction_id' ), 20 );

		add_action( 'wp_logout', array( $this, 'destroy_session' ) );

		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ) );
		}
	}

	/**
	 * Transaction queue workhorse.
	 *
	 * Creates an transaction ID if executing mutations that alter the session data, and stales
	 * execution until the transaction ID is at the top of the queue.
	 *
	 * @param mixed                 $source   Operation root object.
	 * @param array                 $args     Operation arguments.
	 * @param \WPGraphQL\AppContext $context  AppContext instance.
	 * @param \GraphQL\ResolveInfo  $info     Operation ResolveInfo object.
	 */
	public function update_transaction_queue( $source, $args, $context, $info ) {
		// All mutations that alter the session data.
		$session_mutations = array(
			'cart',
			'addToCart',
			'updateItemQuantities',
			'addFee',
			'applyCoupon',
			'removeCoupons',
			'emptyCart',
			'removeItemsFromCart',
			'restoreCartItems',
			'updateItemQuantities',
			'updateShippingMethod',
			'updateCustomer',
		);

		// Bail early, if not one of the session mutations.
		if ( ! in_array( $info->fieldName, $session_mutations, true ) ) { // @codingStandardsIgnoreLine
			return;
		}

		// Initialize transaction ID.
		if ( ! defined( 'WOOGRAPHQL_SESSION_TRANSACTION_ID' ) ) {
			define( 'WOOGRAPHQL_SESSION_TRANSACTION_ID', \uniqid() );
		}

		// Update transaction queue.
		$transaction_queue = $this->get_transaction_queue();

		// Wait until our transaction ID is at the top of the queue before continuing.
		if ( WOOGRAPHQL_SESSION_TRANSACTION_ID !== $transaction_queue[0] ) {
			usleep( 500000 );
			$this->update_transaction_queue( $source, $args, $context, $info );
		}
	}

	/**
	 * Adds transaction ID to the end of the queue, officially starting the transaction,
	 * and returns the transaction queue.
	 *
	 * @return array
	 */
	public function get_transaction_queue() {
		// Get transaction queue.
		$transaction_queue = wp_cache_get( $this->_customer_id, 'woo_session_transactions_queue' );
		if ( ! $transaction_queue ) {
			$transaction_queue = array();
		}

		// If transaction ID not in queue, add it, and start transaction.
		if ( ! in_array( WOOGRAPHQL_SESSION_TRANSACTION_ID, $transaction_queue, true ) ) {
			$transaction_queue[] = WOOGRAPHQL_SESSION_TRANSACTION_ID;

			// Update queue.
			wp_cache_add(
				$this->_customer_id,
				$transaction_queue,
				'woo_session_transactions_queue'
			);
		}

		return $transaction_queue;
	}

	/**
	 * Pop transaction ID off the top of the queue, ending the transaction.
	 *
	 * @throws UserError If transaction ID is not on the top of the queue.
	 */
	public function pop_transaction_id() {
		// Bail if not transaction started.
		if ( ! defined( 'WOOGRAPHQL_SESSION_TRANSACTION_ID' ) ) {
			return;
		}

		// Get transaction queue.
		$transaction_queue = wp_cache_get( $this->_customer_id, 'woo_session_transactions_queue' );

		// Throw if transaction ID not on top.
		if ( WOOGRAPHQL_SESSION_TRANSACTION_ID !== $transaction_queue[0] ) {
			throw new UserError( __( 'Woo session transaction executed out of order', 'wp-graphql-woocommerce' ) );
		} else {

			// Remove Transaction ID and update queue.
			array_shift( $transaction_queue );
			wp_cache_add(
				$this->_customer_id,
				$transaction_queue,
				'woo_session_transactions_queue'
			);
		}
	}

	/**
	 * Setup token and customer ID.
	 *
	 * @throws UserError Invalid token.
	 */
	public function init_session_token() {
		$token = $this->get_session_token();

		// Process existing session.
		if ( $token && ! is_wp_error( $token ) ) {
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

				$this->save_data( $guest_session_id );
				$this->set_customer_session_token( true );
			}

			// Update session if its close to expiring.
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
				$this->update_session_timestamp( $this->_customer_id, $this->_session_expiration );
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
		}
	}

	/**
	 * Retrieve and decrypt the session data from session, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @throws \Exception  Invalid token.
	 * @return bool|array
	 */
	public function get_session_token() {
		// Get the Auth header.
		$session_header = $this->get_session_header();

		if ( empty( $session_header ) ) {
			return false;
		}

		list( $token ) = sscanf( $session_header, 'Session %s' );

		/**
		 * Try to decode the token
		 */
		try {
			JWT::$leeway = 60;

			$secret = $this->get_secret_key();
			$token  = ! empty( $token ) ? JWT::decode( $token, $secret, array( 'HS256' ) ) : null;

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
		}

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
	 * Encrypts and sets the session header on-demand (usually after adding an item to the cart).
	 *
	 * Warning: Headers will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Should the session cookie be set.
	 */
	public function set_customer_session_token( $set ) {
		if ( $set ) {
			/**
			 * Determine the "not before" value for use in the token
			 *
			 * @param string  $issued        The timestamp of token was issued.
			 * @param integer $customer_id   Customer ID.
			 * @param array   $session_data  Cart session data.
			 */
			$not_before = apply_filters(
				'graphql_woo_cart_session_not_before',
				$this->_session_issued,
				$this->_customer_id,
				$this->_data
			);

			// Configure the token array, which will be encoded.
			$token = array(
				'iss'  => get_bloginfo( 'url' ),
				'iat'  => $this->_session_issued,
				'nbf'  => $not_before,
				'exp'  => $this->_session_expiration,
				'data' => array(
					'customer_id' => $this->_customer_id,
				),
			);

			/**
			 * Filter the token, allowing for individual systems to configure the token as needed
			 *
			 * @param array   $token         The token array that will be encoded
			 * @param integer $customer_id   ID of customer associated with token.
			 * @param array   $session_data  Session data associated with token.
			 */
			$token = apply_filters(
				'graphql_woocommerce_cart_session_before_token_sign',
				$token,
				$this->_customer_id,
				$this->_data
			);

			// Encode the token.
			JWT::$leeway = 60;
			$token       = JWT::encode( $token, $this->get_secret_key() );

			/**
			 * Filter the token before returning it, allowing for individual systems to override what's returned.
			 *
			 * For example, if the user should not be granted a token for whatever reason, a filter could have the token return null.
			 *
			 * @param string  $token         The signed JWT token that will be returned
			 * @param integer $customer_id   ID of customer associated with token.
			 * @param array   $session_data  Session data associated with token.
			 */
			$token = apply_filters(
				'graphql_woocommerce_cart_session_signed_token',
				$token,
				$this->_customer_id,
				$this->_data
			);

			if ( ! $token ) {
				return;
			}

			/**
			 * Set session token for use in the HTTP response header and customer/user "sessionToken" field.
			 */
			add_filter(
				'graphql_response_headers_to_send',
				function( $headers ) use ( $token ) {
					$headers[ $this->_token ] = $token;
					return $headers;
				},
				10
			);
			add_filter(
				'graphql_customer_session_token',
				function( $_ ) use ( $token ) {
					return $token;
				}
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
	 */
	public function set_session_expiration() {
		$this->_session_issued = time();
		// 48 Hours.
		$this->_session_expiration = apply_filters(
			'graphql_woocommerce_cart_session_expire',
			time() + ( 3600 * 48 )
		);
		// 47 Hours.
		$this->_session_expiring = $this->_session_expiration - ( 3600 );
	}

	/**
	 * Forget all session data without destroying it.
	 */
	public function forget_session() {
		if ( isset( $this->_token_to_be_sent ) ) {
			unset( $this->_token_to_be_sent );
		}
		wc_empty_cart();
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}
}
