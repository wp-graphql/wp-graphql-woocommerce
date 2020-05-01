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
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Customer_Type
 */
class Customer_Type {

	/**
	 * Registers Customer WPObject type
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
					'customerId'            => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the user. Equivalent to WP_User->ID', 'wp-graphql-woocommerce' ),
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
				),
			)
		);

		register_graphql_field(
			'RootQuery',
			'customer',
			array(
				'type'        => 'Customer',
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'         => array(
						'type'        => 'ID',
						'description' => __( 'Get the customer by their global ID', 'wp-graphql-woocommerce' ),
					),
					'customerId' => array(
						'type'        => 'Int',
						'description' => __( 'Get the customer by their database ID', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context ) {
					$customer_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
							throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
						}

						$customer_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['customerId'] ) ) {
						$customer_id = absint( $args['customerId'] );
					}

					$authorized = ! empty( $customer_id )
						&& ! current_user_can( 'list_users' )
						&& get_current_user_id() !== $customer_id;
					if ( $authorized ) {
						throw new UserError( __( 'Not authorized to access this customer', 'wp-graphql-woocommerce' ) );
					}

					if ( $customer_id ) {
						return Factory::resolve_customer( $customer_id, $context );
					}

					return Factory::resolve_session_customer();
				},
			)
		);
	}
}
