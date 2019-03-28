<?php
/**
 * WPObject Type - Customer
 *
 * Registers WPObject type for WooCommerce customers
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

/**
 * Class Customer
 */
class Customer {
	/**
	 * Registers Customer WPObject type
	 */
	public static function register() {
		register_graphql_object_type(
			'Customer',
			array(
				'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
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
						'type'        => 'String',
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
				'args'        => [
					'id' => [
						'type' => [
							'non_null' => 'ID',
						],
					],
				],
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );
					if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}

					$customer_id = absint( $id_components['id'] );
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
				'args'        => [
					'customerId' => [
						'type' => [
							'non_null' => 'Int',
						],
					],
				],
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$customer_id = absint( $args['id'] );
					return Factory::resolve_customer( $customer_id, $context );
				},
			)
		);
	}
}
