<?php
/**
 * This file contains access functions for various class methods
 *
 * @package WPGraphQL\WooCommerce
 * @since 0.0.1
 */

use WPGraphQL\WooCommerce\Utils\QL_Session_Handler;
use WPGraphQL\WooCommerce\Utils\Transfer_Session_Handler;

if ( ! function_exists( 'wc_graphql_starts_with' ) ) {
	/**
	 * Checks if source string starts with the target string
	 *
	 * @param string $haystack - Source string.
	 * @param string $needle - Target string.
	 *
	 * @return bool
	 */
	function wc_graphql_starts_with( $haystack, $needle ) {
		$length = strlen( $needle );
		return ( substr( $haystack, 0, $length ) === $needle );
	}
}

if ( ! function_exists( 'wc_graphql_ends_with' ) ) {
	/**
	 * Checks if source string ends with the target string
	 *
	 * @param string $haystack - Source string.
	 * @param string $needle - Target string.
	 *
	 * @return bool
	 */
	function wc_graphql_ends_with( $haystack, $needle ) {
		$length = strlen( $needle );
		if ( 0 === $length ) {
			return true;
		}

		return ( substr( $haystack, -$length ) === $needle );
	}
}

if ( ! function_exists( 'wc_graphql_map_tax_statements' ) ) {
	/**
	 * Returns formatted array of tax statement objects.
	 *
	 * @param array $raw_taxes - array of raw taxes object from WC_Order_Item crud objects.
	 *
	 * @return array
	 */
	function wc_graphql_map_tax_statements( $raw_taxes ) {
		$taxes = [];
		foreach ( $raw_taxes as $field => $values ) {
			foreach ( $values as $id => $amount ) {
				if ( empty( $taxes[ $id ] ) ) {
					$taxes[ $id ] = [];
				}
				$taxes[ $id ]['ID']     = $id;
				$taxes[ $id ][ $field ] = $amount;
			}
		}

		return array_values( $taxes );
	}
}//end if

if ( ! function_exists( 'wc_graphql_get_order_statuses' ) ) {
	/**
	 * Get order statuses without prefixes.
	 *
	 * @return array
	 */
	function wc_graphql_get_order_statuses() {
		$order_statuses = [];
		foreach ( array_keys( wc_get_order_statuses() ) as $status ) {
			$order_statuses[] = str_replace( 'wc-', '', $status );
		}
		return $order_statuses;
	}
}

if ( ! function_exists( 'wc_graphql_price' ) ) {
	/**
	 * Format the price with a currency symbol.
	 *
	 * @param  float|string $price Raw price.
	 * @param  array        $args  Arguments to format a price {
	 *            Array of arguments.
	 *            Defaults to empty array.
	 *
	 *     @type string $currency           Currency code.
	 *                                      Defaults to empty string (Use the result from get_woocommerce_currency()).
	 *     @type string $decimal_separator  Decimal separator.
	 *                                      Defaults the result of wc_get_price_decimal_separator().
	 *     @type string $thousand_separator Thousand separator.
	 *                                      Defaults the result of wc_get_price_thousand_separator().
	 *     @type string $decimals           Number of decimals.
	 *                                      Defaults the result of wc_get_price_decimals().
	 *     @type string $price_format       Price format depending on the currency position.
	 *                                      Defaults the result of get_woocommerce_price_format().
	 * }
	 * @return string
	 */
	function wc_graphql_price( $price, $args = [] ) {
		$price = floatval( $price );
		$args  = apply_filters(
			'wc_price_args', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			wp_parse_args(
				$args,
				[
					'currency'           => '',
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
					'price_format'       => get_woocommerce_price_format(),
				]
			)
		);

		$unformatted_price = $price;
		$negative          = $price < 0;

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$price = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );

		$price = apply_filters(
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			'formatted_woocommerce_price',
			number_format(
				$price,
				$args['decimals'],
				$args['decimal_separator'],
				$args['thousand_separator']
			),
			$price,
			$args['decimals'],
			$args['decimal_separator'],
			$args['thousand_separator']
		);

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
			$price = wc_trim_zeros( $price );
		}

		// phpcs:ignore PHPCompatibility.ParameterValues.NewHTMLEntitiesEncodingDefault.NotSet
		$symbol = html_entity_decode( get_woocommerce_currency_symbol( $args['currency'] ) );
		$return = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], $symbol, $price );

		/**
		 * Filters the string of price markup.
		 *
		 * @param string $return            Price HTML markup.
		 * @param string $price             Formatted price.
		 * @param array  $args              Pass on the args.
		 * @param float  $unformatted_price Price as float to allow plugins custom formatting.
		 * @param string $symbol            Currency symbol.
		 */
		return apply_filters( 'graphql_woocommerce_price', $return, $price, $args, $unformatted_price, $symbol );
	}
}//end if

if ( ! function_exists( 'wc_graphql_price_range' ) ) {
	/**
	 * Format a price range for display.
	 *
	 * @param  string|float $from Price from.
	 * @param  string|float $to   Price to.
	 * @return string
	 */
	function wc_graphql_price_range( $from, $to ) {
		if ( $from === $to ) {
			return wc_graphql_price( $from );
		}

		$price = sprintf(
			/* translators: 1: price from 2: price to */
			_x( '%1$s %2$s %3$s', 'Price range: from-to', 'wp-graphql-woocommerce' ),
			is_numeric( $from ) ? wc_graphql_price( $from ) : $from,
			apply_filters( 'graphql_woocommerce_format_price_range_separator', '-', $from, $to ),
			is_numeric( $to ) ? wc_graphql_price( $to ) : $to
		);

		return apply_filters( 'graphql_woocommerce_format_price_range', $price, $from, $to );
	}
}//end if

if ( ! function_exists( 'wc_graphql_underscore_to_camel_case' ) ) {
	/**
	 * Converts a camel case formatted string to a underscore formatted string.
	 *
	 * @param string  $string      String to be formatted.
	 * @param boolean $capitalize  Capitalize first letter of string.
	 *
	 * @return string
	 */
	function wc_graphql_underscore_to_camel_case( $string, $capitalize = false ) {
		$str = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $string ) ) );

		if ( ! $capitalize ) {
			$str[0] = strtolower( $str[0] );
		}

		return $str;
	}
}

if ( ! function_exists( 'wc_graphql_camel_case_to_underscore' ) ) {
	/**
	 * Converts a camel case formatted string to a underscore formatted string.
	 *
	 * @param string $string  String to be formatted.
	 *
	 * @return string
	 */
	function wc_graphql_camel_case_to_underscore( $string ) {
		preg_match_all(
			'!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!',
			$string,
			$matches
		);

		$ret = $matches[0];

		foreach ( $ret as &$match ) {
			$match = strtoupper( $match ) === $match ? strtolower( $match ) : lcfirst( $match );
		}

		return implode( '_', $ret );
	}
}//end if

if ( ! function_exists( 'woographql_setting' ) ) :
	/**
	 * Get an option value from WooGraphQL settings
	 *
	 * @param string $option_name  The key of the option to return.
	 * @param mixed  $default      The default value the setting should return if no value is set.
	 * @param string $section_name The settings section name.
	 *
	 * @return mixed|string|int|boolean
	 */
	function woographql_setting( string $option_name, $default = '', $section_name = 'woographql_settings' ) {
		$section_fields = get_option( $section_name );

		/**
		 * Filter the section fields
		 *
		 * @param array  $section_fields The values of the fields stored for the section
		 * @param string $section_name   The name of the section
		 * @param mixed  $default        The default value for the option being retrieved
		 */
		$section_fields = apply_filters( 'woographql_settings_section_fields', $section_fields, $section_name, $default );

		/**
		 * Get the value from the stored data, or return the default
		 */
		if ( is_array( $default ) ) {
			$value = is_array( $section_fields ) && ! empty( $section_fields[ $option_name ] ) ? $section_fields[ $option_name ] : $default;
		} else {
			$value = isset( $section_fields[ $option_name ] ) ? $section_fields[ $option_name ] : $default;
		}

		/**
		 * Filter the value before returning it
		 *
		 * @param mixed  $value          The value of the field
		 * @param mixed  $default        The default value if there is no value set
		 * @param string $option_name    The name of the option
		 * @param array  $section_fields The setting values within the section
		 * @param string $section_name   The name of the section the setting belongs to
		 */
		return apply_filters( 'woographql_settings_section_field_value', $value, $default, $option_name, $section_fields, $section_name );
	}
endif;

if ( ! function_exists( 'woographql_get_session_uid' ) ) :
	/**
	 * Returns end-user's customer ID.
	 *
	 * @return int
	 */
	function woographql_get_session_uid() {
		/**
		 * Session Handler
		 *
		 * @var QL_Session_Handler|Transfer_Session_Handler $session
		 */
		$session = WC()->session;
		return $session->get_customer_id();
	}
endif;

if ( ! function_exists( 'woographql_get_session_token' ) ) :
	/**
	 * Returns session user's "client_session_id"
	 *
	 * @return string
	 */
	function woographql_get_session_token() {
		/**
		 * Session Handler
		 *
		 * @var QL_Session_Handler|Transfer_Session_Handler $session
		 */
		$session = WC()->session;
		return $session->get_client_session_id();
	}
endif;

if ( ! function_exists( 'woographql_create_nonce' ) ) :
	/**
	 * Creates WooGraphQL session transfer nonces.
	 *
	 * @param string|int $action  Nonce name.
	 *
	 * @return string The nonce.
	 */
	function woographql_create_nonce( $action = -1 ) {
		$uid   = woographql_get_session_uid();
		$token = woographql_get_session_token();
		$i     = wp_nonce_tick( $action );

		return substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	}
endif;

if ( ! function_exists( 'woographql_verify_nonce' ) ) :
	/**
	 * Validate WooGraphQL session transfer nonces.
	 *
	 * @param string         $nonce   Nonce to validated.
	 * @param integer|string $action  Nonce name.
	 *
	 * @return false|int
	 */
	function woographql_verify_nonce( $nonce, $action = -1 ) {
		$nonce = (string) $nonce;
		$uid   = woographql_get_session_uid();

		if ( empty( $nonce ) ) {
			return false;
		}

		$token = woographql_get_session_token();
		$i     = wp_nonce_tick( $action );

		// Nonce generated 0-12 hours ago.
		$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 1;
		}

		// Nonce generated 12-24 hours ago.
		$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 2;
		}

		/**
		 * Fires when nonce verification fails.
		 *
		 * @since 4.4.0
		 *
		 * @param string     $nonce  The invalid nonce.
		 * @param string|int $action The nonce action.
		 * @param string|int $uid    User ID.
		 * @param string     $token  The user's session token.
		 */
		do_action( 'graphql_verify_nonce_failed', $nonce, $action, $uid, $token );

		// Invalid nonce.
		return false;
	}
endif;











