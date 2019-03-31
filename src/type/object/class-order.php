<?php
/**
 * WPObject Type - Order
 *
 * Registers Order WPObject type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Order
 */
class Order {
	/**
	 * Register Order type and queries to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'Order',
			array(
				'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'                  => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the order', 'wp-graphql-woocommerce' ),
					),
					'orderId'             => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
					),
					'orderKey'            => array(
						'type'        => 'String',
						'description' => __( 'Order key', 'wp-graphql-woocommerce' ),
					),
					'currency'            => array(
						'type'        => 'String',
						'description' => __( 'Order currency', 'wp-graphql-woocommerce' ),
					),
					'paymentMethod'       => array(
						'type'        => 'String',
						'description' => __( 'Payment method', 'wp-graphql-woocommerce' ),
					),
					'paymentMethodTitle'  => array(
						'type'        => 'String',
						'description' => __( 'Payment method title', 'wp-graphql-woocommerce' ),
					),
					'transactionId'       => array(
						'type'        => 'String',
						'description' => __( 'Transaction ID', 'wp-graphql-woocommerce' ),
					),
					'customerIpAddress'   => array(
						'type'        => 'String',
						'description' => __( 'Customer IP Address', 'wp-graphql-woocommerce' ),
					),
					'customerUserAgent'   => array(
						'type'        => 'String',
						'description' => __( 'Customer User Agent', 'wp-graphql-woocommerce' ),
					),
					'createdVia'          => array(
						'type'        => 'String',
						'description' => __( 'How order was created', 'wp-graphql-woocommerce' ),
					),
					'dateCompleted'       => array(
						'type'        => 'String',
						'description' => __( 'Date order was completed', 'wp-graphql-woocommerce' ),
					),
					'datePaid'            => array(
						'type'        => 'String',
						'description' => __( 'Date order was paid', 'wp-graphql-woocommerce' ),
					),
					'discountTotal'       => array(
						'type'        => 'Float',
						'description' => __( 'Discount total amount', 'wp-graphql-woocommerce' ),
					),
					'discountTax'         => array(
						'type'        => 'Float',
						'description' => __( 'Discount tax amount', 'wp-graphql-woocommerce' ),
					),
					'shippingTotal'       => array(
						'type'        => 'Float',
						'description' => __( 'Shipping total amount', 'wp-graphql-woocommerce' ),
					),
					'shippingTax'         => array(
						'type'        => 'Float',
						'description' => __( 'Shipping tax amount', 'wp-graphql-woocommerce' ),
					),
					'cartTax'             => array(
						'type'        => 'Float',
						'description' => __( 'Cart tax amount', 'wp-graphql-woocommerce' ),
					),
					'total'               => array(
						'type'        => 'Float',
						'description' => __( 'Order grand total', 'wp-graphql-woocommerce' ),
					),
					'totalTax'            => array(
						'type'        => 'Float',
						'description' => __( 'Order taxes', 'wp-graphql-woocommerce' ),
					),
					'subtotal'            => array(
						'type'        => 'Float',
						'description' => __( 'Order subtotal', 'wp-graphql-woocommerce' ),
					),
					'orderNumber'         => array(
						'type'        => 'String',
						'description' => __( 'Order number', 'wp-graphql-woocommerce' ),
					),
					'orderVersion'        => array(
						'type'        => 'String',
						'description' => __( 'Order version', 'wp-graphql-woocommerce' ),
					),
					'pricesIncludeTax'    => array(
						'type'        => 'Boolean',
						'description' => __( 'Prices include taxes?', 'wp-graphql-woocommerce' ),
					),
					'cartHash'            => array(
						'type'        => 'String',
						'description' => __( 'Cart hash', 'wp-graphql-woocommerce' ),
					),
					'customerNote'        => array(
						'type'        => 'String',
						'description' => __( 'Customer note', 'wp-graphql-woocommerce' ),
					),
					'isDownloadPermitted' => array(
						'type'        => 'Boolean',
						'description' => __( 'Is product download is permitted', 'wp-graphql-woocommerce' ),
					),
					'billing'             => array(
						'type'        => 'CustomerAddress',
						'description' => __( 'Order billing properties', 'wp-graphql-woocommerce' ),
					),
					'shipping'            => array(
						'type'        => 'CustomerAddress',
						'description' => __( 'Order shipping properties', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);

		register_graphql_field(
			'RootQuery',
			'order',
			array(
				'type'        => 'Order',
				'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id' => array(
						'type' => array(
							'non_null' => 'ID',
						),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );
					if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}
					$order_id = absint( $id_components['id'] );
					return Factory::resolve_crud_object( $order_id, $context );
				},
			)
		);

		$post_by_args = array(
			'id'       => array(
				'type'        => 'ID',
				'description' => __( 'Get the order by its global ID', 'wp-graphql-woocommerce' ),
			),
			'orderId' => array(
				'type'        => 'Int',
				'description' => __( 'Get the order by its database ID', 'wp-graphql-woocommerce' ),
			),
			'orderNumber'      => array(
				'type'        => 'String',
				'description' => __( 'Get the order by its order number', 'wp-graphql-woocommerce' ),
			),
		);

		register_graphql_field(
			'RootQuery',
			'orderBy',
			array(
				'type'        => 'Order',
				'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
				'args'        => $post_by_args,
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$order_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}
						$order_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['orderId'] ) ) {
						$order_id = absint( $args['orderId'] );
					} elseif ( ! empty( $args['orderNumber'] ) ) {
						$order_id = 0;
					}

					$order = Factory::resolve_crud_object( $order_id, $context );
					if ( get_post( $order_id )->post_type !== 'shop_order' ) {
						/* translators: not order found error message */
						throw new UserError( sprintf( __( 'No order exists with this id: %1$s' ), $order_id ) );
					}

					return $order;
				},
			)
		);
	}
}
