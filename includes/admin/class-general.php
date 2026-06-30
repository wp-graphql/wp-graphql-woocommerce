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
				'account_url'            => woographql_setting( 'account_url_nonce_param', '_wc_account' ),
				'add_payment_method_url' => woographql_setting( 'add_payment_method_url_nonce_param', '_wc_payment' ),
			]
		);

		return array_values( array_diff_key( $nonce_values, [ $excluded => '' ] ) );
	}

	/**
	 * Returns the enabled authorizing URL fields.
	 *
	 * @return array
	 */
	public static function enabled_authorizing_url_fields_value() {
		return apply_filters(
			'woographql_enabled_authorizing_url_fields',
			[
				'cart_url'               => 'cart_url',
				'checkout_url'           => 'checkout_url',
				'account_url'            => 'account_url',
				'add_payment_method_url' => 'add_payment_method_url',
			]
		);
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
		$all_urls_checked               = self::enabled_authorizing_url_fields_value();

		$cart_url_hardcoded               = defined( 'CART_URL_NONCE_PARAM' ) && ! empty( constant( 'CART_URL_NONCE_PARAM' ) );
		$checkout_url_hardcoded           = defined( 'CHECKOUT_URL_NONCE_PARAM' ) && ! empty( constant( 'CHECKOUT_URL_NONCE_PARAM' ) );
		$account_url_hardcoded            = defined( 'ACCOUNT_URL_NONCE_PARAM' ) && ! empty( constant( 'ACCOUNT_URL_NONCE_PARAM' ) );
		$add_payment_method_url_hardcoded = defined( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) && ! empty( constant( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) );

		$enable_auth_urls_hardcoded = defined( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' ) && ! empty( constant( 'WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS' ) );

		return [
			[
				'name'     => 'disable_ql_session_handler',
				'label'    => __( 'Disable QL Session Handler', 'graphql-for-ecommerce' ),
				'desc'     => __( 'The QL Session Handler takes over management of WooCommerce Session Management on WPGraphQL request replacing the usage of HTTP Cookies with JSON Web Tokens.', 'graphql-for-ecommerce' )
					. ( defined( 'NO_QL_SESSION_HANDLER' ) ? __( ' This setting is disabled. The "NO_QL_SESSION_HANDLER" flag has been triggered with code', 'graphql-for-ecommerce' ) : '' ),
				'type'     => 'checkbox',
				'value'    => defined( 'NO_QL_SESSION_HANDLER' ) ? 'on' : woographql_setting( 'disable_ql_session_handler', 'off' ),
				'disabled' => defined( 'NO_QL_SESSION_HANDLER' ),
			],
			[
				'name'     => 'enable_ql_session_handler_on_ajax',
				'label'    => __( 'Enable QL Session Handler on WC AJAX requests.', 'graphql-for-ecommerce' ),
				'desc'     => __( 'Enabling this will enable JSON Web Tokens usage on WC AJAX requests.', 'graphql-for-ecommerce' )
					. ( defined( 'NO_QL_SESSION_HANDLER' ) ? __( ' This setting is disabled. The "NO_QL_SESSION_HANDLER" flag has been triggered with code', 'graphql-for-ecommerce' ) : '' ),
				'type'     => 'checkbox',
				'value'    => defined( 'NO_QL_SESSION_HANDLER' ) ? 'off' : woographql_setting( 'enable_ql_session_handler_on_ajax', 'off' ),
				'disabled' => defined( 'NO_QL_SESSION_HANDLER' ),
			],
			[
				'name'     => 'enable_ql_session_handler_on_rest',
				'label'    => __( 'Enable QL Session Handler on WP REST requests.', 'graphql-for-ecommerce' ),
				'desc'     => __( 'Enabling this will enable JSON Web Tokens usage on WP REST requests.', 'graphql-for-ecommerce' )
					. ( defined( 'NO_QL_SESSION_HANDLER' ) ? __( ' This setting is disabled. The "NO_QL_SESSION_HANDLER" flag has been triggered with code', 'graphql-for-ecommerce' ) : '' ),
				'type'     => 'checkbox',
				'value'    => defined( 'NO_QL_SESSION_HANDLER' ) ? 'off' : woographql_setting( 'enable_ql_session_handler_on_rest', 'off' ),
				'disabled' => defined( 'NO_QL_SESSION_HANDLER' ),
			],
			[
				'name'     => 'set_session_token_type',
				'label'    => __( 'Session Token Type', 'graphql-for-ecommerce' ),
				'desc'     => __( 'Choose which session token type(s) to generate. "Legacy" uses GraphQL session tokens only. "Store API" uses WooCommerce Blocks Cart-Token only (requires WooCommerce 5.5.0+). "Both" generates both token types for maximum compatibility with headless implementations using WooCommerce Blocks.', 'graphql-for-ecommerce' )
					. ( defined( 'NO_QL_SESSION_HANDLER' ) ? __( ' This setting is disabled. The "NO_QL_SESSION_HANDLER" flag has been triggered with code', 'graphql-for-ecommerce' ) : '' ),
				'type'     => 'select',
				'options'  => [
					'legacy'    => __( 'Legacy (GraphQL Session Token only)', 'graphql-for-ecommerce' ),
					'store-api' => __( 'Store API (Cart-Token only)', 'graphql-for-ecommerce' ),
					'both'      => __( 'Both (GraphQL + Store API)', 'graphql-for-ecommerce' ),
				],
				'default'  => 'legacy',
				'disabled' => defined( 'NO_QL_SESSION_HANDLER' ),
			],
			[
				'name'     => 'session_transfer_behavior',
				'label'    => __( 'Session Transfer Behavior', 'graphql-for-ecommerce' ),
				'desc'     => __( 'Controls how cart data is handled when a user logs in with an existing session from another device. "Keep new" keeps the current session data (default). "Keep old" restores the previously saved session data. "Merge" combines cart items from both sessions.', 'graphql-for-ecommerce' ),
				'type'     => 'select',
				'options'  => [
					'keep_new_fallback_old' => __( 'Keep new, fallback to old (default)', 'graphql-for-ecommerce' ),
					'keep_new'              => __( 'Keep new (always use current session)', 'graphql-for-ecommerce' ),
					'keep_old'              => __( 'Keep old (restore previously saved session)', 'graphql-for-ecommerce' ),
				],
				'default'  => 'keep_new_fallback_old',
				'disabled' => defined( 'NO_QL_SESSION_HANDLER' ),
			],
			[
				'name'     => 'enable_transliteration',
				'label'    => __( 'Transliterate non-latin characters', 'graphql-for-ecommerce' ),
				'desc'     => __( 'Converts non-latin characters (Cyrillic, Chinese, Arabic, etc.) to their latin equivalents in GraphQL type and enum names. Enable this if your WooCommerce tax classes, product attributes, or taxonomies use non-latin names. Requires the PHP intl extension.', 'graphql-for-ecommerce' )
					. ( ! function_exists( 'transliterator_transliterate' ) ? __( ' <strong>Warning:</strong> The PHP intl extension is not available. This setting will have no effect.', 'graphql-for-ecommerce' ) : '' ),
				'type'     => 'checkbox',
				'default'  => 'off',
				'disabled' => ! function_exists( 'transliterator_transliterate' ),
			],
			[
				'name'    => 'enable_unsupported_product_type',
				'label'   => __( 'Enable Unsupported types', 'graphql-for-ecommerce' ),
				'desc'    => __( 'Substitute unsupported product types with SimpleProduct', 'graphql-for-ecommerce' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'              => 'enable_authorizing_url_fields',
				'label'             => __( 'Enable User Session transferring URLs', 'graphql-for-ecommerce' ),
				'desc'              => __( 'URL fields to add to the <strong>Customer</strong> type.', 'graphql-for-ecommerce' )
					. ( $enable_auth_urls_hardcoded ? __( ' This setting is disabled. The "WPGRAPHQL_WOOCOMMERCE_ENABLE_AUTH_URLS" flag has been triggered with code', 'graphql-for-ecommerce' ) : '' ),
				'type'              => 'multicheck',
				'options'           => apply_filters(
					'woographql_settings_enable_authorizing_url_options',
					[
						'cart_url'               => __( 'Cart URL. Field name: <strong>cartUrl</strong>', 'graphql-for-ecommerce' ),
						'checkout_url'           => __( 'Checkout URL. Field name: <strong>checkoutUrl</strong>', 'graphql-for-ecommerce' ),
						'account_url'            => __( 'Account URL. Field name: <strong>accountUrl</strong>', 'graphql-for-ecommerce' ),
						'add_payment_method_url' => __( 'Add Payment Method URL. Field name: <strong>addPaymentMethodUrl</strong>', 'graphql-for-ecommerce' ),
					]
				),
				'value'             => $enable_auth_urls_hardcoded ? $all_urls_checked : woographql_setting( 'enable_authorizing_url_fields', [] ),
				'disabled'          => $enable_auth_urls_hardcoded,
				'sanitize_callback' => static function ( $value ) {
					if ( empty( $value ) ) {
						return [];
					}

					return $value;
				},
			],
			[
				'name'     => 'authorizing_url_endpoint',
				'label'    => __( 'Endpoint for Authorizing URLs', 'graphql-for-ecommerce' ),
				'desc'     => sprintf(
					/* translators: %1$s: Site URL, %2$s: WooGraphQL Auth Endpoint */
					__( 'The endpoint (path) for transferring user sessions on the site. <a target="_blank" href="%1$s/%2$s">%1$s/%2$s</a>.', 'graphql-for-ecommerce' ),
					site_url(),
					woographql_setting( 'authorizing_url_endpoint', 'transfer-session' )
				),
				'type'     => 'text',
				'default'  => ! empty( $custom_endpoint ) ? $custom_endpoint : 'transfer-session',
				'disabled' => empty( $enabled_authorizing_url_fields ),
			],
			[
				'name'              => 'cart_url_nonce_param',
				'label'             => __( 'Cart URL nonce name', 'graphql-for-ecommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "cartUrl" field', 'graphql-for-ecommerce' )
					. ( $cart_url_hardcoded ? __( ' This setting is disabled. The "CART_URL_NONCE_PARAM" flag has been set with code', 'graphql-for-ecommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $cart_url_hardcoded ? constant( 'CART_URL_NONCE_PARAM' ) : woographql_setting( 'cart_url_nonce_param', '_wc_cart' ),
				'disabled'          => defined( 'CART_URL_NONCE_PARAM' ) || ! in_array( 'cart_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => static function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'cart_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'cart_url_nonce_param',
							'unique',
							__( 'The <strong>Cart URL nonce name</strong> field must be unique', 'graphql-for-ecommerce' ),
							'error'
						);

						return '_wc_cart';
					}

					return $value;
				},
			],
			[
				'name'              => 'checkout_url_nonce_param',
				'label'             => __( 'Checkout URL nonce name', 'graphql-for-ecommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "checkoutUrl" field', 'graphql-for-ecommerce' )
					. ( $checkout_url_hardcoded ? __( ' This setting is disabled. The "CHECKOUT_URL_NONCE_PARAM" flag has been set with code', 'graphql-for-ecommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $checkout_url_hardcoded ? constant( 'CHECKOUT_URL_NONCE_PARAM' ) : woographql_setting( 'checkout_url_nonce_param', '_wc_checkout' ),
				'disabled'          => defined( 'CHECKOUT_URL_NONCE_PARAM' ) || ! in_array( 'checkout_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => static function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'checkout_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'checkout_url_nonce_param',
							'unique',
							__( 'The <strong>Checkout URL nonce name</strong> field must be unique', 'graphql-for-ecommerce' ),
							'error'
						);

						return '_wc_checkout';
					}

					return $value;
				},
			],
			[
				'name'              => 'account_url_nonce_param',
				'label'             => __( 'Account URL nonce name', 'graphql-for-ecommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "accountUrl" field', 'graphql-for-ecommerce' )
					. ( $account_url_hardcoded ? __( ' This setting is disabled. The "ACCOUNT_URL_NONCE_PARAM" flag has been set with code', 'graphql-for-ecommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $account_url_hardcoded ? constant( 'ACCOUNT_URL_NONCE_PARAM' ) : woographql_setting( 'account_url_nonce_param', '_wc_account' ),
				'disabled'          => defined( 'ACCOUNT_URL_NONCE_PARAM' ) || ! in_array( 'account_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => static function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'account_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'account_url_nonce_param',
							'unique',
							__( 'The <strong>Account URL nonce name</strong> field must be unique', 'graphql-for-ecommerce' ),
							'error'
						);

						return '_wc_account';
					}

					return $value;
				},
			],
			[
				'name'              => 'add_payment_method_url_nonce_param',
				'label'             => __( 'Add Payment Method URL nonce name', 'graphql-for-ecommerce' ),
				'desc'              => __( 'Query parameter name of the nonce included in the "addPaymentMethodUrl" field', 'graphql-for-ecommerce' )
					. ( $add_payment_method_url_hardcoded ? __( ' This setting is disabled. The "ADD_PAYMENT_METHOD_URL_NONCE_PARAM" flag has been set with code', 'graphql-for-ecommerce' ) : '' ),
				'type'              => 'text',
				'value'             => $add_payment_method_url_hardcoded ? constant( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) : woographql_setting( 'add_payment_method_url_nonce_param', '_wc_payment' ),
				'disabled'          => defined( 'ADD_PAYMENT_METHOD_URL_NONCE_PARAM' ) || ! in_array( 'add_payment_method_url', $enabled_authorizing_url_fields, true ),
				'sanitize_callback' => static function ( $value ) {
					$other_nonces = self::get_other_nonce_values( 'add_payment_method_url' );
					if ( in_array( $value, $other_nonces, true ) ) {
						add_settings_error(
							'add_payment_method_url_nonce_param',
							'unique',
							__( 'The <strong>Add Payment Method URL nonce name</strong> field must be unique', 'graphql-for-ecommerce' ),
							'error'
						);

						return '_wc_payment';
					}

					return $value;
				},
			],
			[
				'name'    => 'enable_pre_auth_download_urls',
				'label'   => __( 'Enable pre-authenticated download URLs', 'graphql-for-ecommerce' ),
				'desc'    => __( 'Adds a "preAuthDownloadUrl" field to downloadable items that generates a tokenized URL allowing downloads without cookie-based authentication. Useful for headless frontends where users cannot be redirected through the session transfer endpoint.', 'graphql-for-ecommerce' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'    => 'download_url_nonce_param',
				'label'   => __( 'Download URL nonce name', 'graphql-for-ecommerce' ),
				'desc'    => __( 'Query parameter name of the nonce included in the "downloadUrl" field on downloadable items.', 'graphql-for-ecommerce' ),
				'type'    => 'text',
				'default' => '_wc_download',
			],
		];
	}
}
