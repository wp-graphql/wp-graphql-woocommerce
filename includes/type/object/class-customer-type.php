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
	public static function get_fields( $other_fields = array() ) {
		return array_merge(
			array(
				'id'                    => array(
					'type'        => array( 'non_null' => 'ID' ),
					'description' => __( 'The globally unique identifier for the customer', 'wp-graphql-woocommerce' ),
				),
				'databaseId'            => array(
					'type'        => 'Int',
					'description' => __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						$database_id = absint( $source->ID );
						return ! empty( $database_id ) ? $database_id : null;
					},
				),
				'isVatExempt'           => array(
					'type'        => 'Boolean',
					'description' => __( 'Is customer VAT exempt?', 'wp-graphql-woocommerce' ),
				),
				'hasCalculatedShipping' => array(
					'type'        => 'Boolean',
					'description' => __( 'Has calculated shipping?', 'wp-graphql-woocommerce' ),
				),
				'calculatedShipping'    => array(
					'type'        => 'Boolean',
					'description' => __( 'Has customer calculated shipping?', 'wp-graphql-woocommerce' ),
				),
				'lastOrder'             => array(
					'type'        => 'Order',
					'description' => __( 'Gets the customers last order.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						return Factory::resolve_crud_object( $source->last_order_id, $context );
					},
				),
				'orderCount'            => array(
					'type'        => 'Int',
					'description' => __( 'Return the number of orders this customer has.', 'wp-graphql-woocommerce' ),
				),
				'totalSpent'            => array(
					'type'        => 'Float',
					'description' => __( 'Return how much money this customer has spent.', 'wp-graphql-woocommerce' ),
				),
				'username'              => array(
					'type'        => 'String',
					'description' => __( 'Return the customer\'s username.', 'wp-graphql-woocommerce' ),
				),
				'email'                 => array(
					'type'        => 'String',
					'description' => __( 'Return the customer\'s email.', 'wp-graphql-woocommerce' ),
				),
				'firstName'             => array(
					'type'        => 'String',
					'description' => __( 'Return the customer\'s first name.', 'wp-graphql-woocommerce' ),
				),
				'lastName'              => array(
					'type'        => 'String',
					'description' => __( 'Return the customer\'s last name.', 'wp-graphql-woocommerce' ),
				),
				'displayName'           => array(
					'type'        => 'String',
					'description' => __( 'Return the customer\'s display name.', 'wp-graphql-woocommerce' ),
				),
				'role'                  => array(
					'type'        => 'String',
					'description' => __( 'Return the customer\'s user role.', 'wp-graphql-woocommerce' ),
				),
				'date'                  => array(
					'type'        => 'String',
					'description' => __( 'Return the date customer was created', 'wp-graphql-woocommerce' ),
				),
				'modified'              => array(
					'type'        => 'String',
					'description' => __( 'Return the date customer was last updated', 'wp-graphql-woocommerce' ),
				),
				'billing'               => array(
					'type'        => 'CustomerAddress',
					'description' => __( 'Return the date customer billing address properties', 'wp-graphql-woocommerce' ),
				),
				'shipping'              => array(
					'type'        => 'CustomerAddress',
					'description' => __( 'Return the date customer shipping address properties', 'wp-graphql-woocommerce' ),
				),
				'isPayingCustomer'      => array(
					'type'        => 'Boolean',
					'description' => __( 'Return the date customer was last updated', 'wp-graphql-woocommerce' ),
				),
				'metaData'              => Meta_Data_Type::get_metadata_field_definition(),
				'session'               => array(
					'type'        => array( 'list_of' => 'MetaData' ),
					'description' => __( 'Session data for the viewing customer', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						/**
						 * Session Handler.
						 *
						 * @var \WC_Session_Handler $session
						 */
						$session = \WC()->session;

						if ( (string) $session->get_customer_id() === (string) $source->ID ) {
							$session_data = $session->get_session_data();
							$session      = array();
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
				),
			),
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
	public static function get_connections( $other_connections = array() ) {
		return array_merge(
			array(
				'downloadableItems' => array(
					'toType'         => 'DownloadableItem',
					'connectionArgs' => array(
						'active'                => array(
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that can be downloaded now.', 'wp-graphql-woocommerce' ),
						),
						'expired'               => array(
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that are expired.', 'wp-graphql-woocommerce' ),
						),
						'hasDownloadsRemaining' => array(
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that have downloads remaining.', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Downloadable_Item_Connection_Resolver( $source, $args, $context, $info );

						return $resolver->get_connection();
					},
				),
			),
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
			array(
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
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
			)
		);

		/**
		 * Register "availablePaymentMethods" field to "Customer" type.
		 */
		register_graphql_fields(
			'Customer',
			array(
				'availablePaymentMethods'   => array(
					'type'        => array( 'list_of' => 'PaymentToken' ),
					'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							return array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) );
						}

						if ( get_current_user_id() === 0 ) {
							return array();
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				),
				'availablePaymentMethodsCC' => array(
					'type'        => array( 'list_of' => 'PaymentTokenCC' ),
					'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
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
							return array();
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				),
				'availablePaymentMethodsEC' => array(
					'type'        => array( 'list_of' => 'PaymentTokenECheck' ),
					'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
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
							return array();
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				),
			)
		);
	}

	/**
	 * Registers fields that require the "QL_Session_Handler" class to work.
	 *
	 * @return void
	 */
	public static function register_session_handler_fields() {
		/**
		 * Register the "sessionToken" field to the "Customer" type.
		 */
		register_graphql_field(
			'Customer',
			'sessionToken',
			array(
				'type'        => 'String',
				'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					if ( \get_current_user_id() === $source->ID || 'guest' === $source->id ) {
						/**
						 * Session handler.
						 *
						 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
						 */
						$session = \WC()->session;

						return apply_filters( 'graphql_customer_session_token', $session->build_token() );
					}

					return null;
				},
			)
		);
		/**
		 * Register the "wooSessionToken" field to the "User" type.
		 */
		register_graphql_field(
			'User',
			'wooSessionToken',
			array(
				'type'        => 'String',
				'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					if ( \get_current_user_id() === $source->userId ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						/**
						 * Session handler
						 *
						 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session
						 */
						$session = \WC()->session;

						return apply_filters( 'graphql_customer_session_token', $session->build_token() );
					}

					return null;
				},
			)
		);
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
				array(
					'cartUrl'   => array(
						'type'        => 'String',
						'description' => __( 'A nonced link to the cart page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
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
							$query_params = array(
								'session_id' => $customer_id,
								$nonce_name  => woographql_create_nonce( "load-cart_{$customer_id}" ),
							);
							$query_params = apply_filters( 'graphql_cart_url_query_params', $query_params, $customer_id, $source );
							$url          = add_query_arg(
								$query_params,
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					),
					'cartNonce' => array(
						'type'        => 'String',
						'description' => __( 'A nonce for the cart page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
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
					),
				)
			);
		}//end if

		if ( in_array( 'checkout_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				array(
					'checkoutUrl'   => array(
						'type'        => 'String',
						'description' => __( 'A nonce link to the checkout page for session user. Expires in 24 hours.', 'wp-graphql-woocommerce' ),
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
							$query_params = array(
								'session_id' => $customer_id,
								$nonce_name  => woographql_create_nonce( "load-checkout_{$customer_id}" ),
							);
							$query_params = apply_filters( 'graphql_checkout_url_query_params', $query_params, $customer_id, $source );
							$url          = add_query_arg(
								$query_params,
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					),
					'checkoutNonce' => array(
						'type'        => 'String',
						'description' => __( 'A nonce for the checkout page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
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
					),
				)
			);
		}//end if

		if ( in_array( 'account_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				array(
					'accountUrl'   => array(
						'type'        => 'String',
						'description' => __( 'A nonce link to the account page for session user. Expires in 24 hours.', 'wp-graphql-woocommerce' ),
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
							$query_params = array(
								'session_id' => $customer_id,
								$nonce_name  => woographql_create_nonce( "load-account_{$customer_id}" ),
							);
							$query_params = apply_filters( 'graphql_account_url_query_params', $query_params, $customer_id, $source );
							$url          = add_query_arg(
								$query_params,
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					),
					'accountNonce' => array(
						'type'        => 'String',
						'description' => __( 'A nonce for the account page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
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
					),
				)
			);
		}//end if

		if ( in_array( 'add_payment_method_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				array(
					'addPaymentMethodUrl'   => array(
						'type'        => 'String',
						'description' => __( 'A nonce link to the add payment method page for the authenticated user. Expires in 24 hours.', 'wp-graphql-woocommerce' ),
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
								array(
									'session_id' => $customer_id,
									$nonce_name  => woographql_create_nonce( "add-payment-method_{$customer_id}" ),
								),
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					),
					'addPaymentMethodNonce' => array(
						'type'        => 'String',
						'description' => __( 'A nonce for the add payment method page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
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
					),
				)
			);
		}//end if
	}
}
