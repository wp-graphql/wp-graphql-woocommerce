<?php
/**
 * Defines WooGraphQL's general settings.
 *
 * @package WPGraphQL\WooCommerce\Admin
 */

namespace WPGraphQL\WooCommerce\Admin;

/**
 * General class
 */
class General extends Section {

	/**
	 * Returns the other nonce values besides the one provided.
	 *
	 * @param string $excluded  Slug of nonce value to be excluded.
	 *
	 * @return array
	 */
	public static function get_other_nonce_values( $excluded ) {
		$nonce_values = apply_filters(
			'woographql_authorizing_url_nonce_values',
			[
				'cart_url'               => woographql_setting( 'cart_url_nonce_param', '_wc_cart' ),
				'checkout_url'           => woographql_setting( 'checkout_url_nonce_param', '_wc_checkout' ),
				'add_payment_method_url' => woographql_setting( 'add_payment_method_url_nonce_param', '_wc_payment' ),
			]
		);

		return array_values( array_diff_key( $nonce_values, [ $excluded => '' ] ) );
	}

	/**
	 * Returns General settings fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		$custom_endpoint                = apply_filters( 'woographql_authorizing_url_endpoint', null );
		$enabled_authorizing_url_fields = woographql_setting( 'enable_authorizing_url_fields', [] );
		$enabled_authorizing_url_fields = ! empty( $enabled_authorizing_url_fields ) ? array_keys( $enabled_authorizing_url_fields ) : [];
		$all_urls_checked               = apply_filters(
			'woographql_enabled_authorizing_url_fields',
			[
				'cart_url'               => 'cart_url',
				'checkout_url'           => 'checkout_url',
				'add_payment_method_url' => 'add_payment_method_url',
			]
		);

		$cart_url_hardcoded               = defined( 'CART_URL_NONCE_PARAM' ) && ! empty( constant( 'CART_URL_NONCE_PARAM' ) );
		$checkout_url_hardcoded           = defined( 'CHECKOUT_URL_NONCE_PARAM' ) && ! empty( constant( 'CHECKOUT_URL_NONCE_PARAM' ) );
		$add_payment_method_url_hardcoded = defined( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) && ! empty( constant( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) );

		$enable_auth_urls_hardcoded = defined( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' ) && ! empty( constant( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) );

		return [
			[
				'name'     => 'disable_ql_session_handler',
				'label'    => __( 'Disable QL Session Handler', 'wp-graphql-woocommerce' ),
				'desc'     => __( 'The QL Session Handler takes over management of WooCommerce Session Management on WPGraphQL request replacing the usage of HTTP Cookies with JSON Web Tokens.', 'wp-graphql-woocommerce' )
					. ( defined( 'NO_QL_SESSION_HANDLER' ) ? __( ' This setting is disabled. The "NO_QL_SESSION_HANDLER" flag has been triggered with code', 'wp-graphql-woocommerce' ) : '' ),
				'type'     => 'checkbox',
				'value'    => defined( 'NO_QL_SESSION_HANDLER' ) ? 'on' : woographql_setting( 'disable_ql_session_handler', 'off' ),
				'disabled' => defined( 'NO_QL_SESSION_HANDLER' ) ? true : false,
			],
			[
				'name'    => 'enable_unsupported_product_type',
				'label'   => __( 'Enable Unsupported types', 'wp-graphql-woocommerce' ),
				'desc'    => __( 'Substitute unsupported product types with SimpleProduct', 'wp-graphql-woocommerce' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'              => 'enable_authorizing_url_fields',
				'label'             => __( 'Enable User Session transferring URLs', 'wp-graphql-woocommerce' ),
				'desc'              => __( 'URL fields to add to the <strong>Customer</strong> type.', 'wp-graphql-woocommerce' )
					. ( $enable_auth_urls_hardcoded ? __( ' This setting is disabled. The "WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS" flag has been triggered with code', 'wp-graphql-woocommerce' ) : '' ),
				'type'              => 'multicheck',
				'options'           => apply_filters(
					'woographql_settings_enable_authorizing_url_options',
					[
						'cart_url'               => __( 'Cart URL. Field name: <strong>cartUrl</strong>', 'wp-graphql-woocommerce' ),
						'checkout_url'           => __( 'Checkout URL. Field name: <strong>checkoutUrl</strong>', 'wp-graphql-woocommerce' ),
						'add_payment_method_url' => __( 'Add Payment Method URL. Field name: <strong>addPaymentMethodUrl</strong>', 'wp-graphql-woocommerce' ),
					]
				),
				'value'             => $enable_auth_urls_hardcoded ? $all_urls_checked : woographql_setting( 'enable_authorizing_url_fields', [] ),
				'disabled'          => $enable_auth_urls_hardcoded ? true : false,
				'sanitize_callback' => function( $value ) {
					if ( empty( $value ) ) {
						return [];
					}

					return $value;
				},
			],
			[
				'name'     => 'authorizing_url_endpoint',
				'label'    => __( 'Endpoint for Authorizing URLs', 'wp-graphql-woocommerce' ),
				'desc'     => sprintf(
					/* translators: %1$s: Site URL, %2$s: WooGraphQL Auth Endpoint */
					__( 'The endpoint (path) for transferring user sessions on the site. <a target="_blank" href="%1$s/%2$s">%1$s/%2$s</a>.', 'wp-graphql-woocommerce' ),
					site_url(),
					woographql_setting( 'authorizing_url_endpoint', 'transfer-session' )
				),
				'type'     => 'text',
				'default'  => ! empty( $custom_endpoint ) ? $custom_endpoint : 'transfer-session',
				'disabled' => empty( $enabled_authorizing_url_fields ),
			],
			[
				'name'              => 'cart_url_nonce_param',
				'label'             => __( 'Cart URL nonce name', 'wp-graphql-woocommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "cartUrl" field', 'wp-graphql-woocommerce' )
					. ( $cart_url_hardcoded ? __( ' This setting is disabled. The "CART_URL_NONCE_PARAM" flag has been set with code', 'wp-graphql-woocommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $cart_url_hardcoded ? CART_URL_NONCE_PARAM : woographql_setting( 'cart_url_nonce_param', '_wc_cart' ),
				'disabled'          => defined( 'CART_URL_NONCE_PARAM' ) || ! in_array( 'cart_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'cart_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'cart_url_nonce_param',
							'unique',
							__( 'The <strong>Cart URL nonce name</strong> field must be unique', 'wp-graphql-woocommerce' ),
							'error'
						);

						return '_wc_cart';
					}

					return $value;
				},
			],
			[
				'name'              => 'checkout_url_nonce_param',
				'label'             => __( 'Checkout URL nonce name', 'wp-graphql-woocommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "checkoutUrl" field', 'wp-graphql-woocommerce' )
					. ( $checkout_url_hardcoded ? __( ' This setting is disabled. The "CHECKOUT_URL_NONCE_PARAM" flag has been set with code', 'wp-graphql-woocommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $checkout_url_hardcoded ? CHECKOUT_URL_NONCE_PARAM : woographql_setting( 'checkout_url_nonce_param', '_wc_checkout' ),
				'disabled'          => defined( 'CHECKOUT_URL_NONCE_PARAM' ) || ! in_array( 'checkout_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'checkout_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'checkout_url_nonce_param',
							'unique',
							__( 'The <strong>Checkout URL nonce name</strong> field must be unique', 'wp-graphql-woocommerce' ),
							'error'
						);

						return '_wc_checkout';
					}

					return $value;
				},
			],
			[
				'name'              => 'add_payment_method_url_nonce_param',
				'label'             => __( 'Add Payment Method URL nonce name', 'wp-graphql-woocommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "addPaymentMethodUrl" field', 'wp-graphql-woocommerce' )
					. ( $add_payment_method_url_hardcoded ? __( ' This setting is disabled. The "ADD_PAYMENT_METHOD_URL_NONCE_PARAM" flag has been set with code', 'wp-graphql-woocommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $add_payment_method_url_hardcoded ? ADD_PAYMENT_METHOD_URL_NONCE_PARAM : woographql_setting( 'add_payment_method_url_nonce_param', '_wc_payment' ),
				'disabled'          => defined( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) || ! in_array( 'add_payment_method_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'add_payment_method_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'add_payment_method_url_nonce_param',
							'unique',
							__( 'The <strong>Add Payment Method URL nonce name</strong> field must be unique', 'wp-graphql-woocommerce' ),
							'error'
						);

						return '_wc_payment';
					}

					return $value;
				},
			],
		];
	}
}
