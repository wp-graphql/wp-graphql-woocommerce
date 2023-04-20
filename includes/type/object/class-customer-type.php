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

/**
 * Class Customer_Type
 */
class Customer_Type {

	/**
	 * Returns the "Customer" type fields.
	 *
	 * @param array $other_fields Extra fields configs to be added or override the default field definitions.
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
			],
			$other_fields,
		);
	}

	/**
	 * Returns the "Customer" type connections.
	 *
	 * @param array $other_connections Extra connections configs to be added or override the default connection definitions.
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

		// Register session token fields if QL_Session_Handler is enabled.
		if ( 'off' === woographql_setting( 'disable_ql_session_handler', 'off' ) ) {
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
							return apply_filters( 'graphql_customer_session_token', \WC()->session->build_token() );
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
							return apply_filters( 'graphql_customer_session_token', \WC()->session->build_token() );
						}

						return null;
					},
				]
			);
		}

		/**
		 * Register "availablePaymentMethods" field to "Customer" type.
		 */
		register_graphql_field(
			'Customer',
			'availablePaymentMethods',
			[
				'type'        => [ 'list_of' => 'PaymentToken' ],
				'description' => __( 'Customer\'s stored payment tokens.', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source ) {
					if ( \get_current_user_id() === $source->ID ) {
						return \WC_Payment_Tokens::get_customer_tokens( $customer_id );
					}

					throw new UserError( __( 'Not authorized to view this user\'s payment methods.', 'wp-graphql-woocommerce' ) );
				},
			]
		);
	}
}
