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
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Customer_Type
 */
class Customer_Type {

	/**
	 * Registers Customer WPObject type and related fields.
	 */
	public static function register() {
		register_graphql_object_type(
			'Customer',
			array(
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array(
					'id'                    => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the customer', 'wp-graphql-woocommerce' ),
					),
					'databaseId'            => array(
						'type'        => 'Int',
						'description' => __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' ),
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
						'resolve'     => function( $source, array $args, AppContext $context ) {
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
					'sessionToken'          => array(
						'type'        => 'String',
						'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							if ( $source->ID === \get_current_user_id() ) {
								return apply_filters( 'graphql_customer_session_token', null );
							}

							return null;
						},
					)
				),
			)
		);

		/**
		 * Register the "sessionToken" field to the "User" type.
		 */
		register_graphql_field(
			'User',
			'sessionToken',
			array(
				'type'        => 'String',
				'description' => __( 'A JWT token that can be used in future requests to for WooCommerce session identification', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source ) {
					if ( $source->ID === \get_current_user_id() ) {
						return apply_filters( 'graphql_customer_session_token', null );
					}

					return null;
				},
			)
		);
	}
}
