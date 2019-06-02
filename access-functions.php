<?php
/**
 * This file contains access functions for various class methods
 *
 * @package WPGraphQL\Extensions\WooCommerce
 * @since 0.0.1
 */

/**
 * Adds an ObjectType to the TypeRegistry and node resolvers to Relay node definitions.
 *
 * @param string $type_name The name of the Type to register.
 * @param array  $config    The Type config.
 */
function wc_register_graphql_object_type( $type_name, $config ) {
	$config['kind'] = 'object';
	if ( ! empty( $config['resolve_node'] ) ) {
		add_filter( 'graphql_resolve_node', $config['resolve_node'], 10, 4 );
		unset( $config['resolve_node'] );
	}
	if ( ! empty( $config['resolve_node_type'] ) ) {
		add_filter( 'graphql_resolve_node_type', $config['resolve_node_type'], 10, 2 );
		unset( $config['resolve_node_type'] );
	}
	register_graphql_type( $type_name, $config );
}

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
		'wc_price_args',
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
	$price             = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
	$price             = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );

	if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
		$price = wc_trim_zeros( $price );
	}

	$symbol = html_entity_decode( get_woocommerce_currency_symbol( $args['currency'] ) );
	return ( $negative ? '-' : '' ) . sprintf( $args['price_format'], $symbol, $price );

	/**
	 * Filters the string of price markup.
	 *
	 * @param string $return            Price HTML markup.
	 * @param string $price             Formatted price.
	 * @param array  $args              Pass on the args.
	 * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
	 */
	return apply_filters( 'wc_price', $return, $price, $args, $unformatted_price );
}