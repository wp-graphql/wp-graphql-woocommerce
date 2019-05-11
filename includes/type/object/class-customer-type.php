<?php
/**
 * WPObject Type - Customer_Type
 *
 * Registers WPObject type for WooCommerce customers
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;

/**
 * Class Customer_Type
 */
class Customer_Type {
	/**
	 * Registers Customer WPObject type
	 */
	public static function register() {
		wc_register_graphql_object_type(
			'Customer',
			array(
				'description'       => __( 'A customer object', 'wp-graphql-woocommerce' ),
				'interfaces'        => [ WPObjectType::node_interface() ],
				'fields'            => array(
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
				'resolve_node'      => function( $node, $id, $type, $context ) {
					if ( 'customer' === $type ) {
						$node = Factory::resolve_customer( $id, $context );
					}

					return $node;
				},
				'resolve_node_type' => function( $type, $node ) {
					if ( is_a( $node, Customer::class ) ) {
						$type = 'Customer';
					}

					return $type;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'customer',
			array(
				'type'        => 'Customer',
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id' => array(
						'type' => 'ID',
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$customer_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
							throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
						}

						$customer_id = absint( $id_components['id'] );
					} elseif ( isset( $context->viewer->ID ) && ! empty( $context->viewer->ID ) ) {
						$customer_id = $context->viewer->ID;
					}

					if ( ! $customer_id ) {
						throw new UserError( __( 'You must be logged in to access customer fields', 'wp-graphql-woocommerce' ) );
					}

					return Factory::resolve_customer( $customer_id, $context );
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'customerBy',
			array(
				'type'        => 'Customer',
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'customerId' => array(
						'type'        => array( 'non_null' => 'Int' ),
						'description' => __( 'Get the customer by their database ID', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					if ( empty( $args['customerId'] ) ) {
						throw new UserError( __( 'customerId must be provided and it must be an integer value', 'wp-graphql-woocommerce' ) );
					}
					$customer_id = absint( $args['customerId'] );
					return Factory::resolve_customer( $customer_id, $context );
				},
			)
		);
	}
}
