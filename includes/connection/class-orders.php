<?php
/**
 * Connection - Orders
 *
 * Registers connections to Order
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Order_Connection_Resolver;

/**
 * Class - Orders
 */
class Orders {

	/**
	 * Registers the various connections from other Types to Customer
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'connectionArgs' => self::get_connection_args( 'private' ),
					'resolve'        => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$post_type_obj = get_post_type_object( 'shop_order' );
						$not_manager   = ! current_user_can( $post_type_obj->cap->edit_posts );

						if ( $not_manager ) {
							$public_args = array_keys( self::get_connection_args( 'public' ) );
							$args = array_filter(
								$args,
								function( $key ) use ( $public_args ) {
									return in_array( $key, $public_args, true );
								},
								ARRAY_FILTER_USE_KEY
							);
						}

						$resolver = new Order_Connection_Resolver( $source, $args, $context, $info );

						$customer_id = get_current_user_id();
						if ( $not_manager && 0 !== $customer_id ) {
							$resolver->set_query_arg( 'customer_id', $customer_id );
						}

						return $resolver->get_connection();
					},
				)
			)
		);

		// From Customer.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Customer',
					'fromFieldName' => 'orders',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Order_Connection_Resolver( $source, $args, $context, $info );
						$resolver->set_query_arg( 'customer_id', $source->ID );

						return $resolver->get_connection();
					}
				)
			)
		);
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		return array_merge(
			array(
				'fromType'       => 'RootQuery',
				'toType'         => 'Order',
				'fromFieldName'  => 'orders',
				'connectionArgs' => self::get_connection_args(),
			),
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @param string $access Connection argument access-level.
	 * @return array
	 */
	public static function get_connection_args( $access = 'public' ): array {
		switch ( $access ) {
			case 'private':
				return array_merge(
					get_wc_cpt_connection_args(),
					array(
						'statuses'    => array(
							'type'        => array( 'list_of' => 'OrderStatusEnum' ),
							'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
						),
						'customerId'  => array(
							'type'        => 'Int',
							'description' => __( 'Limit result set to orders assigned a specific customer.', 'wp-graphql-woocommerce' ),
						),
						'customersIn' => array(
							'type'        => array( 'list_of' => 'Int' ),
							'description' => __( 'Limit result set to orders assigned a specific group of customers.', 'wp-graphql-woocommerce' ),
						),
						'productId'   => array(
							'type'        => 'Int',
							'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
						),
						'orderby'     => array(
							'type'        => array( 'list_of' => 'OrdersOrderbyInput' ),
							'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
						),
					)
				);

			case 'public':
			default:
				return array(
					'statuses'  => array(
						'type'        => array( 'list_of' => 'OrderStatusEnum' ),
						'description' => __( 'Limit result set to orders assigned a specific status.', 'wp-graphql-woocommerce' ),
					),
					'productId' => array(
						'type'        => 'Int',
						'description' => __( 'Limit result set to orders assigned a specific product.', 'wp-graphql-woocommerce' ),
					),
					'orderby'   => array(
						'type'        => array( 'list_of' => 'OrdersOrderbyInput' ),
						'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
					),
					'search'    => array(
						'type'        => 'String',
						'description' => __( 'Limit results to those matching a string.', 'wp-graphql-woocommerce' ),
					),
					'dateQuery' => array(
						'type'        => 'DateQueryInput',
						'description' => __( 'Filter the connection based on dates.', 'wp-graphql-woocommerce' ),
					),
				);
		}
	}
}
