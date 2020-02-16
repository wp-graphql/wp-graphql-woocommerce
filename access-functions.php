<?php
/**
 * This file contains access functions for various class methods
 *
 * @package WPGraphQL\WooCommerce
 * @since 0.0.1
 */

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

/**
 * Returns formatted array of tax statement objects.
 *
 * @param array $raw_taxes - array of raw taxes object from WC_Order_Item crud objects.
 *
 * @return array
 */
function wc_graphql_map_tax_statements( $raw_taxes ) {
	$taxes = array();
	foreach ( $raw_taxes as $field => $values ) {
		foreach ( $values as $id => $amount ) {
			if ( empty( $taxes[ $id ] ) ) {
				$taxes[ $id ] = array();
			}
			$taxes[ $id ]['ID']     = $id;
			$taxes[ $id ][ $field ] = $amount;
		}
	}

	return array_values( $taxes );
}

/**
 * Get order statuses without prefixes.
 *
 * @return array
 */
function wc_graphql_get_order_statuses() {
	$order_statuses = array();
	foreach ( array_keys( wc_get_order_statuses() ) as $status ) {
		$order_statuses[] = str_replace( 'wc-', '', $status );
	}
	return $order_statuses;
}

/**
 * Format the price with a currency symbol.
 *
 * @param  float $price Raw price.
 * @param  array $args  Arguments to format a price {
 *     Array of arguments.
 *     Defaults to empty array.
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
function wc_graphql_price( $price, $args = array() ) {
	$args = apply_filters(
		'wc_price_args', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		wp_parse_args(
			$args,
			array(
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			)
		)
	);

	$unformatted_price = $price;
	$negative          = $price < 0;

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	$price             = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) ); 


	$price             = apply_filters(
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
	 * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
	 */
	return apply_filters( 'graphql_woocommerce_price', $return, $price, $args, $unformatted_price, $symbol );
}

/**
 * Format a price range for display.
 *
 * @param  string $from Price from.
 * @param  string $to   Price to.
 * @return string
 */
function wc_graphql_price_range( $from, $to ) {
	$price = sprintf(
		/* translators: 1: price from 2: price to */
		_x( '%1$s %2$s %3$s', 'Price range: from-to', 'wp-graphql-woocommerce' ),
		is_numeric( $from ) ? wc_graphql_price( $from ) : $from,
		apply_filters( 'graphql_woocommerce_format_price_range_separator', '-', $from, $to ),
		is_numeric( $to ) ? wc_graphql_price( $to ) : $to
	);

	return apply_filters( 'graphql_woocommerce_format_price_range', $price, $from, $to );
}
