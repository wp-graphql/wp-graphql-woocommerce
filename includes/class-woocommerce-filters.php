<?php
/**
 * Adds filters that modify woocommerce functionality on GraphQL requests.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;

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
	 *
	 * @return void
	 */
	public static function setup() {
		self::$session_header = apply_filters( 'graphql_woocommerce_cart_session_http_header', 'woocommerce-session' );

		// Check if request is a GraphQL POST request.
		if ( ! self::is_session_handler_disabled() ) {
			add_filter( 'woocommerce_session_handler', [ self::class, 'woocommerce_session_handler' ] );
			add_filter( 'graphql_response_headers_to_send', [ self::class, 'add_session_header_to_expose_headers' ] );
			add_filter( 'graphql_access_control_allow_headers', [ self::class, 'add_session_header_to_allow_headers' ] );
		}

		// Add better support for Stripe payment gateway.
		add_filter( 'graphql_stripe_process_payment_args', [ self::class, 'woographql_stripe_gateway_args' ], 10, 2 );

		// WPGraphQL Reset password -> Use woocommerce email password template when requested
		add_filter( 'retrieve_password_message', [ self::class, 'get_reset_password_message' ], 10, 3 );
		add_filter( 'retrieve_password_title', [ self::class, 'get_reset_password_title' ] );
	}

	/**
	 * Returns true if the "Disable QL Session Handler" option is checked on the settings page.
	 *
	 * @return boolean
	 */
	public static function is_session_handler_disabled() {
		return defined( 'NO_QL_SESSION_HANDLER' ) || 'on' === woographql_setting( 'disable_ql_session_handler', 'off' );
	}

	/**
	 * Returns array of enabled authorizing URL field slugs.
	 *
	 * @return array
	 */
	public static function enabled_authorizing_url_fields() {
		if ( defined( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' ) ) {
			return \WPGraphQL\WooCommerce\Admin\General::enabled_authorizing_url_fields_value();
		}
		return woographql_setting( 'enable_authorizing_url_fields', [] );
	}

	/**
	 * Return the nonce query parameter name for the provided field.
	 *
	 * @param string $field  URL field slug.
	 *
	 * @return string null
	 */
	public static function get_authorizing_url_nonce_param_name( $field ) {
		$flag_name      = strtoupper( $field );
		$hardcoded_name = defined( "{$flag_name}_NONCE_PARAM" ) ? constant( "{$flag_name}_NONCE_PARAM" ) : false;
		if ( ! empty( $hardcoded_name ) ) {
			return $hardcoded_name;
		}

		return woographql_setting( "{$field}_nonce_param", null );
	}

	/**
	 * WooCommerce Session Handler callback
	 *
	 * @param string $session_class  Class name of WooCommerce Session Handler.
	 * @return string
	 */
	public static function woocommerce_session_handler( $session_class ) {
		if ( \WPGraphQL\Router::is_graphql_http_request() ) {
			$session_class = '\WPGraphQL\WooCommerce\Utils\QL_Session_Handler';
		} elseif ( WooGraphQL::auth_router_is_enabled() ) {
			require_once get_includes_directory() . 'utils/class-protected-router.php';
			require_once get_includes_directory() . 'utils/class-transfer-session-handler.php';

			$session_class = Utils\Protected_Router::is_auth_request() ? '\WPGraphQL\WooCommerce\Utils\Transfer_Session_Handler' : $session_class;
		}

		return $session_class;
	}

	/**
	 * Append session header to the exposed headers in GraphQL responses
	 *
	 * @param array $headers GraphQL responser headers.
	 * @return array
	 */
	public static function add_session_header_to_expose_headers( array $headers ) {
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
	 * @return array
	 */
	public static function add_session_header_to_allow_headers( array $allowed_headers ) {
		$allowed_headers[] = self::$session_header;
		return $allowed_headers;
	}

	/**
	 * Adds extra arguments to the Stripe Gateway process payment call.
	 *
	 * @param array  $gateway_args    Arguments to be passed to the gateway `process_payment` method.
	 * @param string $payment_method  Payment gateway ID.
	 *
	 * @return array
	 */
	public static function woographql_stripe_gateway_args( $gateway_args, $payment_method ) {
		if ( 'stripe' === $payment_method ) {
			$gateway_args = [
				$gateway_args[0],
				true,
				false,
				false,
				true,
			];
		}

		return $gateway_args;
	}

	/**
	 * Customizes the password reset message for ResetPassword Mutation.
	 *
	 * This function modifies the password reset message to use WooCommerce's email template
	 * if the `WC_Email_Customer_Reset_Password` email is enabled. It sets the email subject
	 * and content type based on WooCommerce settings and returns the styled email content.
	 *
	 * @param string $message      The original password reset message.
	 * @param string $key          The password reset key.
	 * @param string $user_login   The username or email of the user requesting the password reset.
	 *
	 * @return string              The customized password reset message. Returns the original message if
	 *                             the `WC_Email_Customer_Reset_Password` email is not enabled.
	 */
	public static function get_reset_password_message( $message, $key, $user_login ) {
		/** @var \WC_Email_Customer_Reset_Password|null $wc_reset_email */
		$wc_reset_email = \WC()->mailer()->emails['WC_Email_Customer_Reset_Password'];

		if ( $wc_reset_email && $wc_reset_email->is_enabled() ) {
			add_filter( 'wp_mail_content_type', [ $wc_reset_email, 'get_content_type' ] );

			$wc_reset_email->user_login = $user_login;
			$wc_reset_email->reset_key  = $key;
			$message                    = $wc_reset_email->style_inline( $wc_reset_email->get_content() );
			return $message;
		} else {
			return $message;
		}
	}

	/**
	 * Customizes the password reset title for ResetPassword Mutation.
	 *
	 * This function modifies the password reset email title to use WooCommerce's email subject
	 * if the `WC_Email_Customer_Reset_Password` email is enabled.
	 *
	 * @param string $title The original password reset email title.
	 *
	 * @return string       The customized password reset email title. Returns the original title if
	 *                      the `WC_Email_Customer_Reset_Password` email is not enabled.
	 */
	public static function get_reset_password_title( $title ) {
		/** @var \WC_Email_Customer_Reset_Password|null $wc_reset_email */
		$wc_reset_email = \WC()->mailer()->emails['WC_Email_Customer_Reset_Password'];

		return $wc_reset_email->is_enabled() ? $wc_reset_email->get_subject() : $title;
	}
}
