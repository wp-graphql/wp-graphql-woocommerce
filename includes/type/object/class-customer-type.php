<?php
/**
 * WPObject Type - Customer_Type
 *
 * Registers WPObject type for WooCommerce customers
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Downloadable_Item_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Customer_Type
 */
class Customer_Type {
	/**
	 * Returns the "Customer" type fields.
	 *
	 * @param array $other_fields Extra fields configs to be added or override the default field definitions.
	 *
	 * @return array
	 */
	public static function get_fields( $other_fields = [] ) {
		return array_merge(
			[
				'id'                    => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => static function () {
						return __( 'The globally unique identifier for the customer', 'wp-graphql-woocommerce' );
					},
				],
				'databaseId'            => [
					'type'        => 'Int',
					'description' => static function () {
						return __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						$database_id = absint( $source->ID );
						return ! empty( $database_id ) ? $database_id : null;
					},
				],
				'isVatExempt'           => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Is customer VAT exempt?', 'wp-graphql-woocommerce' );
					},
				],
				'hasCalculatedShipping' => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Has calculated shipping?', 'wp-graphql-woocommerce' );
					},
				],
				'calculatedShipping'    => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Has customer calculated shipping?', 'wp-graphql-woocommerce' );
					},
				],
				'lastOrder'             => [
					'type'        => 'Order',
					'description' => static function () {
						return __( 'Gets the customers last order.', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						return Factory::resolve_crud_object( $source->last_order_id, $context );
					},
				],
				'orderCount'            => [
					'type'        => 'Int',
					'description' => static function () {
						return __( 'Return the number of orders this customer has.', 'wp-graphql-woocommerce' );
					},
				],
				'totalSpent'            => [
					'type'        => 'Float',
					'description' => static function () {
						return __( 'Return how much money this customer has spent.', 'wp-graphql-woocommerce' );
					},
				],
				'username'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the customer\'s username.', 'wp-graphql-woocommerce' );
					},
				],
				'email'                 => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the customer\'s email.', 'wp-graphql-woocommerce' );
					},
				],
				'firstName'             => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the customer\'s first name.', 'wp-graphql-woocommerce' );
					},
				],
				'lastName'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the customer\'s last name.', 'wp-graphql-woocommerce' );
					},
				],
				'displayName'           => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the customer\'s display name.', 'wp-graphql-woocommerce' );
					},
				],
				'role'                  => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the customer\'s user role.', 'wp-graphql-woocommerce' );
					},
				],
				'date'                  => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the date customer was created', 'wp-graphql-woocommerce' );
					},
				],
				'modified'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Return the date customer was last updated', 'wp-graphql-woocommerce' );
					},
				],
				'billing'               => [
					'type'        => 'CustomerAddress',
					'description' => static function () {
						return __( 'Return the date customer billing address properties', 'wp-graphql-woocommerce' );
					},
				],
				'shipping'              => [
					'type'        => 'CustomerAddress',
					'description' => static function () {
						return __( 'Return the date customer shipping address properties', 'wp-graphql-woocommerce' );
					},
				],
				'isPayingCustomer'      => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Return the date customer was last updated', 'wp-graphql-woocommerce' );
					},
				],
				'metaData'              => Meta_Data_Type::get_metadata_field_definition(),
				'session'               => [
					'type'        => [ 'list_of' => 'MetaData' ],
					'description' => static function () {
						return __( 'Session data for the viewing customer', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						/**
						 * Session Handler.
						 *
						 * @var \WC_Session_Handler $session
						 */
						$session = \WC()->session;

						if ( (string) $session->get_customer_id() === (string) $source->ID ) {
							$session_data = $session->get_session_data();
							$session      = [];
							foreach ( $session_data as $key => $value ) {
								$meta        = new \stdClass();
								$meta->id    = null;
								$meta->key   = $key;
								$meta->value = maybe_unserialize( $value );
								$session[]   = $meta;
							}

							return $session;
						}

						throw new UserError( __( 'It\'s not possible to access another user\'s session data', 'wp-graphql-woocommerce' ) );
					},
				],
			],
			$other_fields
		);
	}

	/**
	 * Returns the "Customer" type connections.
	 *
	 * @param array $other_connections Extra connections configs to be added or override the default connection definitions.
	 *
	 * @return array
	 */
	public static function get_connections( $other_connections = [] ) {
		return array_merge(
			[
				'downloadableItems' => [
					'toType'         => 'DownloadableItem',
					'connectionArgs' => [
						'active'                => [
							'type'        => 'Boolean',
							'description' => static function () {
								return __( 'Limit results to downloadable items that can be downloaded now.', 'wp-graphql-woocommerce' );
							},
						],
						'expired'               => [
							'type'        => 'Boolean',
							'description' => static function () {
								return __( 'Limit results to downloadable items that are expired.', 'wp-graphql-woocommerce' );
							},
						],
						'hasDownloadsRemaining' => [
							'type'        => 'Boolean',
							'description' => static function () {
								return __( 'Limit results to downloadable items that have downloads remaining.', 'wp-graphql-woocommerce' );
							},
						],
					],
					'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Downloadable_Item_Connection_Resolver( $source, $args, $context, $info );

						return $resolver->get_connection();
					},
				],
			],
			$other_connections
		);
	}

	/**
	 * Registers Customer WPObject type and related fields.
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'Customer',
			[
				'description' => static function () {
					return __( 'A customer object', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Node' ],
				/**
				 * Allows for a decisive filtering of the order fields.
				 * Note: Only use if deregisteration or renaming the field(s) has failed.
				 *
				 * @param array $fields  Customer field definitions.
				 * @return array
				 */
				'fields'      => apply_filters( 'woographql_customer_field_definitions', self::get_fields() ),
				/**
				 * Allows for a decisive filtering of the order connections.
				 * Note: Only use if deregisteration or renaming the connection(s) has failed.
				 *
				 * @param array $connections  Customer connection definitions.
				 * @return array
				 */
				'connections' => apply_filters( 'woographql_customer_connection_definitions', self::get_connections() ),
			]
		);

		/**
		 * Register "availablePaymentMethods" field to "Customer" type.
		 */
		register_graphql_fields(
			'Customer',
			[
				'availablePaymentMethods'   => [
					'type'        => [ 'list_of' => 'PaymentTokenInterface' ],
					'description' => static function () {
						return __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							return array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) );
						}

						if ( get_current_user_id() === 0 ) {
							return [];
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				],
				'availablePaymentMethodsCC' => [
					'type'        => [ 'list_of' => 'PaymentTokenCC' ],
					'description' => static function () {
						return __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							return array_filter(
								array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) ),
								static function ( $token ) {
									return 'CC' === $token->get_type();
								}
							);
						}

						if ( get_current_user_id() === 0 ) {
							return [];
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				],
				'availablePaymentMethodsEC' => [
					'type'        => [ 'list_of' => 'PaymentTokenECheck' ],
					'description' => static function () {
						return __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							return array_filter(
								array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) ),
								static function ( $token ) {
									return 'eCheck' === $token->get_type();
								}
							);
						}

						if ( get_current_user_id() === 0 ) {
							return [];
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				],
			]
		);
	}

	/**
	 * Registers fields that require the "QL_Session_Handler" class to work.
	 *
	 * @return void
	 */
	public static function register_session_handler_fields() {
		$token_type = woographql_setting( 'set_session_token_type', 'legacy' );
		if ( in_array( $token_type, [ 'legacy', 'both' ], true ) ) {
			/**
			 * Register the "sessionToken" field to the "Customer" type.
			 */
			register_graphql_field(
				'Customer',
				'sessionToken',
				[
					'type'        => 'String',
					'description' => static function () {
						return __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( \get_current_user_id() === $source->ID || 'guest' === $source->id ) {
							return new Deferred(
								static function () {
									/**
									 * Session handler.
									 *
									 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
									 */
									$session = \WC()->session;

									return apply_filters( 'graphql_customer_session_token', $session->build_token() );
								}
							);
						}

						return null;
					},
				]
			);

			/**
			 * Register the "wooSessionToken" field to the "User" type.
			 */
			register_graphql_field(
				'User',
				'wooSessionToken',
				[
					'type'        => 'String',
					'description' => static function () {
						return __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( \get_current_user_id() === $source->userId ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							return new Deferred(
								static function () {
									/**
									 * Session handler
									 *
									 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
									 */
									$session = \WC()->session;

									return apply_filters( 'graphql_customer_session_token', $session->build_token() );
								}
							);
						}

						return null;
					},
				]
			);
		}

		if ( in_array( $token_type, [ 'store-api', 'both' ], true ) ) {
			/**
			 * Register the "cartToken" field to the "Customer" type.
			 */
			register_graphql_field(
				'Customer',
				'cartToken',
				[
					'type'        => 'String',
					'description' => static function () {
						return __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( \get_current_user_id() === $source->ID || 'guest' === $source->id ) {
							return new Deferred(
								static function () {
									/**
									 * Session handler.
									 *
									 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
									 */
									$session = \WC()->session;

									return apply_filters( 'graphql_cart_token', $session->build_cart_token() );
								}
							);
						}

						return null;
					},
				]
			);

			/**
			 * Register the "cartToken" field to the "User" type.
			 */
			register_graphql_field(
				'User',
				'cartToken',
				[
					'type'        => 'String',
					'description' => static function () {
						return __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						if ( \get_current_user_id() === $source->userId ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							return new Deferred(
								static function () {
									/**
									 * Session handler
									 *
									 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
									 */
									$session = \WC()->session;

									return apply_filters( 'graphql_cart_token', $session->build_cart_token() );
								}
							);
						}

						return null;
					},
				]
			);
		}
	}

	/**
	 * Registers selected authorizing_url_fields
	 *
	 * @param array $fields_to_register  Slugs of fields.
	 * @return void
	 */
	public static function register_authorizing_url_fields( $fields_to_register ) {
		if ( in_array( 'cart_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				[
					'cartUrl'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonced link to the cart page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							// Build nonced url as an unauthenticated user.
							$nonce_name   = woographql_setting( 'cart_url_nonce_param', '_wc_cart' );
							$query_params = [
								'session_id' => $customer_id,
								$nonce_name  => woographql_create_nonce( "load-cart_{$customer_id}" ),
							];
							$query_params = apply_filters( 'graphql_cart_url_query_params', $query_params, $customer_id, $source );
							$url          = add_query_arg(
								$query_params,
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'cartNonce' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce for the cart page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							return woographql_create_nonce( "load-cart_{$customer_id}" );
						},
					],
				]
			);
		}//end if

		if ( in_array( 'checkout_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				[
					'checkoutUrl'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce link to the checkout page for session user. Expires in 24 hours.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							// Build nonced url as an unauthenticated user.
							$nonce_name   = woographql_setting( 'checkout_url_nonce_param', '_wc_checkout' );
							$query_params = [
								'session_id' => $customer_id,
								$nonce_name  => woographql_create_nonce( "load-checkout_{$customer_id}" ),
							];
							$query_params = apply_filters( 'graphql_checkout_url_query_params', $query_params, $customer_id, $source );
							$url          = add_query_arg(
								$query_params,
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'checkoutNonce' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce for the checkout page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							return woographql_create_nonce( "load-checkout_{$customer_id}" );
						},
					],
				]
			);
		}//end if

		if ( in_array( 'account_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				[
					'accountUrl'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce link to the account page for session user. Expires in 24 hours.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							if ( ! is_user_logged_in() ) {
								return null;
							}

							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							// Build nonced url as an unauthenticated user.
							$nonce_name   = woographql_setting( 'account_url_nonce_param', '_wc_account' );
							$query_params = [
								'session_id' => $customer_id,
								$nonce_name  => woographql_create_nonce( "load-account_{$customer_id}" ),
							];
							$query_params = apply_filters( 'graphql_account_url_query_params', $query_params, $customer_id, $source );
							$url          = add_query_arg(
								$query_params,
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'accountNonce' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce for the account page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							if ( ! is_user_logged_in() ) {
								return null;
							}

							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							return woographql_create_nonce( "load-account_{$customer_id}" );
						},
					],
				]
			);
		}//end if

		if ( in_array( 'add_payment_method_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				[
					'addPaymentMethodUrl'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce link to the add payment method page for the authenticated user. Expires in 24 hours.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							if ( ! is_user_logged_in() ) {
								return null;
							}

							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( $current_user_id !== $customer_id ) {
								return null;
							}

							// Build nonced url as an unauthenticated user.
							$nonce_name = woographql_setting( 'add_payment_method_url_nonce_param', '_wc_payment' );
							$url        = add_query_arg(
								[
									'session_id' => $customer_id,
									$nonce_name  => woographql_create_nonce( "add-payment-method_{$customer_id}" ),
								],
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'addPaymentMethodNonce' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A nonce for the add payment method page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							if ( ! is_user_logged_in() ) {
								return null;
							}

							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							return woographql_create_nonce( "add-payment-method_{$customer_id}" );
						},
					],
				]
			);
		}//end if
	}
}
