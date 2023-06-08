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
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Data\Connection\Downloadable_Item_Connection_Resolver;
use WPGraphQL\WooCommerce\Utils\QL_Session_Handler;

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
					'description' => __( 'The globally unique identifier for the customer', 'wp-graphql-woocommerce' ),
				],
				'databaseId'            => [
					'type'        => 'Int',
					'description' => __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						$database_id = absint( $source->ID );
						return ! empty( $database_id ) ? $database_id : null;
					},
				],
				'isVatExempt'           => [
					'type'        => 'Boolean',
					'description' => __( 'Is customer VAT exempt?', 'wp-graphql-woocommerce' ),
				],
				'hasCalculatedShipping' => [
					'type'        => 'Boolean',
					'description' => __( 'Has calculated shipping?', 'wp-graphql-woocommerce' ),
				],
				'calculatedShipping'    => [
					'type'        => 'Boolean',
					'description' => __( 'Has customer calculated shipping?', 'wp-graphql-woocommerce' ),
				],
				'lastOrder'             => [
					'type'        => 'Order',
					'description' => __( 'Gets the customers last order.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source, array $args, AppContext $context ) {
						return Factory::resolve_crud_object( $source->last_order_id, $context );
					},
				],
				'orderCount'            => [
					'type'        => 'Int',
					'description' => __( 'Return the number of orders this customer has.', 'wp-graphql-woocommerce' ),
				],
				'totalSpent'            => [
					'type'        => 'Float',
					'description' => __( 'Return how much money this customer has spent.', 'wp-graphql-woocommerce' ),
				],
				'username'              => [
					'type'        => 'String',
					'description' => __( 'Return the customer\'s username.', 'wp-graphql-woocommerce' ),
				],
				'email'                 => [
					'type'        => 'String',
					'description' => __( 'Return the customer\'s email.', 'wp-graphql-woocommerce' ),
				],
				'firstName'             => [
					'type'        => 'String',
					'description' => __( 'Return the customer\'s first name.', 'wp-graphql-woocommerce' ),
				],
				'lastName'              => [
					'type'        => 'String',
					'description' => __( 'Return the customer\'s last name.', 'wp-graphql-woocommerce' ),
				],
				'displayName'           => [
					'type'        => 'String',
					'description' => __( 'Return the customer\'s display name.', 'wp-graphql-woocommerce' ),
				],
				'role'                  => [
					'type'        => 'String',
					'description' => __( 'Return the customer\'s user role.', 'wp-graphql-woocommerce' ),
				],
				'date'                  => [
					'type'        => 'String',
					'description' => __( 'Return the date customer was created', 'wp-graphql-woocommerce' ),
				],
				'modified'              => [
					'type'        => 'String',
					'description' => __( 'Return the date customer was last updated', 'wp-graphql-woocommerce' ),
				],
				'billing'               => [
					'type'        => 'CustomerAddress',
					'description' => __( 'Return the date customer billing address properties', 'wp-graphql-woocommerce' ),
				],
				'shipping'              => [
					'type'        => 'CustomerAddress',
					'description' => __( 'Return the date customer shipping address properties', 'wp-graphql-woocommerce' ),
				],
				'isPayingCustomer'      => [
					'type'        => 'Boolean',
					'description' => __( 'Return the date customer was last updated', 'wp-graphql-woocommerce' ),
				],
				'metaData'              => Meta_Data_Type::get_metadata_field_definition(),
				'session'               => [
					'type'        => [ 'list_of' => 'MetaData' ],
					'description' => __( 'Session data for the viewing customer', 'wp-graphql-woocommerce' ),
					'resolve'     => function ( $source ) {
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
			$other_fields,
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
							'description' => __( 'Limit results to downloadable items that can be downloaded now.', 'wp-graphql-woocommerce' ),
						],
						'expired'               => [
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that are expired.', 'wp-graphql-woocommerce' ),
						],
						'hasDownloadsRemaining' => [
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that have downloads remaining.', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'        => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
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
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
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
					'type'        => [ 'list_of' => 'PaymentToken' ],
					'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							return array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) );
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				],
				'availablePaymentMethodsCC' => [
					'type'        => [ 'list_of' => 'PaymentTokenCC' ],
					'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							$tokens = array_filter(
								array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) ),
								function ( $token ) {
									return 'CC' === $token->get_type();
								}
							);
							return $tokens;
						}

						throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
					},
				],
				'availablePaymentMethodsEC' => [
					'type'        => [ 'list_of' => 'PaymentTokenECheck' ],
					'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						if ( get_current_user_id() === $source->ID ) {
							$tokens = array_filter(
								array_values( \WC_Payment_Tokens::get_customer_tokens( $source->ID ) ),
								function ( $token ) {
									return 'eCheck' === $token->get_type();
								}
							);
							return $tokens;
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
		/**
		 * Register the "sessionToken" field to the "Customer" type.
		 */
		register_graphql_field(
			'Customer',
			'sessionToken',
			[
				'type'        => 'String',
				'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source ) {
					if ( \get_current_user_id() === $source->ID || 'guest' === $source->id ) {
						/**
						 * Session handler.
						 *
						 * @var QL_Session_Handler $session
						 */
						$session = \WC()->session;

						return apply_filters( 'graphql_customer_session_token', $session->build_token() );
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
				'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source ) {
					if ( \get_current_user_id() === $source->userId ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						/**
						 * Session handler
						 *
						 * @var QL_Session_Handler $session
						 */
						$session = \WC()->session;

						return apply_filters( 'graphql_customer_session_token', $session->build_token() );
					}

					return null;
				},
			]
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
				[
					'cartUrl'   => [
						'type'        => 'String',
						'description' => __( 'A nonced link to the cart page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							// Build nonced url as an unauthenticated user.
							$nonce_name = woographql_setting( 'cart_url_nonce_param', '_wc_cart' );
							$url        = add_query_arg(
								[
									'session_id' => $customer_id,
									$nonce_name  => woographql_create_nonce( "load-cart_{$customer_id}" ),
								],
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'cartNonce' => [
						'type'        => 'String',
						'description' => __( 'A nonce for the cart page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
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
						'description' => __( 'A nonce link to the checkout page for session user. Expires in 24 hours.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							// Get current customer and user ID.
							$customer_id     = $source->ID;
							$current_user_id = get_current_user_id();

							// Return null if current user not user being queried.
							if ( 0 !== $current_user_id && $current_user_id !== $customer_id ) {
								return null;
							}

							// Build nonced url as an unauthenticated user.
							$nonce_name = woographql_setting( 'checkout_url_nonce_param', '_wc_checkout' );
							$url        = add_query_arg(
								[
									'session_id' => $customer_id,
									$nonce_name  => woographql_create_nonce( "load-checkout_{$customer_id}" ),
								],
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'checkoutNonce' => [
						'type'        => 'String',
						'description' => __( 'A nonce for the checkout page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
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

		if ( in_array( 'add_payment_method_url', $fields_to_register, true ) ) {
			register_graphql_fields(
				'Customer',
				[
					'addPaymentMethodUrl'   => [
						'type'        => 'String',
						'description' => __( 'A nonce link to the add payment method page for the authenticated user. Expires in 24 hours.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
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
									$nonce_name  => woographql_create_nonce( "load-account_{$customer_id}" ),
								],
								site_url( woographql_setting( 'authorizing_url_endpoint', 'transfer-session' ) )
							);

							return esc_url_raw( $url );
						},
					],
					'addPaymentMethodNonce' => [
						'type'        => 'String',
						'description' => __( 'A nonce for the add payment method page. By default, it expires in 1 hour.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
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
	}
}
