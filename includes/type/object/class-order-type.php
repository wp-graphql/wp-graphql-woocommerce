<?php
/**
 * WPObject Type - Order_Type
 *
 * Registers Order WPObject type and queries
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
 * Class Order_Type
 */
class Order_Type {

	/**
	 * Register Order type and queries to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'Order',
			array(
				'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array(
					'Node',
					'NodeWithComments'
				),
				'fields'      => array(
					'id'                    => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the order', 'wp-graphql-woocommerce' ),
					),
					'databaseId'            => array(
						'type'        => 'Int',
						'description' => __( 'The ID of the order in the database', 'wp-graphql-woocommerce' ),
					),
					'orderKey'              => array(
						'type'        => 'String',
						'description' => __( 'Order key', 'wp-graphql-woocommerce' ),
					),
					'date'                  => array(
						'type'        => 'String',
						'description' => __( 'Date order was created', 'wp-graphql-woocommerce' ),
					),
					'modified'              => array(
						'type'        => 'String',
						'description' => __( 'Date order was last updated', 'wp-graphql-woocommerce' ),
					),
					'currency'              => array(
						'type'        => 'String',
						'description' => __( 'Order currency', 'wp-graphql-woocommerce' ),
					),
					'paymentMethod'         => array(
						'type'        => 'String',
						'description' => __( 'Payment method', 'wp-graphql-woocommerce' ),
					),
					'paymentMethodTitle'    => array(
						'type'        => 'String',
						'description' => __( 'Payment method title', 'wp-graphql-woocommerce' ),
					),
					'transactionId'         => array(
						'type'        => 'String',
						'description' => __( 'Transaction ID', 'wp-graphql-woocommerce' ),
					),
					'customerIpAddress'     => array(
						'type'        => 'String',
						'description' => __( 'Customer IP Address', 'wp-graphql-woocommerce' ),
					),
					'customerUserAgent'     => array(
						'type'        => 'String',
						'description' => __( 'Customer User Agent', 'wp-graphql-woocommerce' ),
					),
					'createdVia'            => array(
						'type'        => 'String',
						'description' => __( 'How order was created', 'wp-graphql-woocommerce' ),
					),
					'dateCompleted'         => array(
						'type'        => 'String',
						'description' => __( 'Date order was completed', 'wp-graphql-woocommerce' ),
					),
					'datePaid'              => array(
						'type'        => 'String',
						'description' => __( 'Date order was paid', 'wp-graphql-woocommerce' ),
					),
					'discountTotal'         => array(
						'type'        => 'String',
						'description' => __( 'Discount total amount', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->discountTotalRaw;
							} else {
								// @codingStandardsIgnoreLine.
								return $source->discountTotal;
							}
						},
					),
					'discountTax'           => array(
						'type'        => 'String',
						'description' => __( 'Discount tax amount', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->discountTaxRaw;
							} else {
								// @codingStandardsIgnoreLine.
								return $source->discountTax;
							}
						},
					),
					'shippingTotal'         => array(
						'type'        => 'String',
						'description' => __( 'Shipping total amount', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->shippingTotalRaw;
							}

							// @codingStandardsIgnoreLine.
							return $source->shippingTotal;
						},
					),
					'shippingTax'           => array(
						'type'        => 'String',
						'description' => __( 'Shipping tax amount', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->shippingTaxRaw;
							}

							// @codingStandardsIgnoreLine.
							return $source->shippingTax;
						},
					),
					'cartTax'               => array(
						'type'        => 'String',
						'description' => __( 'Cart tax amount', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->cartTaxRaw;
							} else {
								// @codingStandardsIgnoreLine.
								return $source->cartTax;
							}
						},
					),
					'total'                 => array(
						'type'        => 'String',
						'description' => __( 'Order grand total', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->totalRaw;
							} else {
								return $source->total;
							}
						},
					),
					'totalTax'              => array(
						'type'        => 'String',
						'description' => __( 'Order taxes', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->totalTaxRaw;
							} else {
								// @codingStandardsIgnoreLine.
								return $source->totalTax;
							}
						},
					),
					'subtotal'              => array(
						'type'        => 'String',
						'description' => __( 'Order subtotal', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->subtotalRaw;
							} else {
								return $source->subtotal;
							}
						},
					),
					'orderNumber'           => array(
						'type'        => 'String',
						'description' => __( 'Order number', 'wp-graphql-woocommerce' ),
					),
					'orderVersion'          => array(
						'type'        => 'String',
						'description' => __( 'Order version', 'wp-graphql-woocommerce' ),
					),
					'pricesIncludeTax'      => array(
						'type'        => 'Boolean',
						'description' => __( 'Prices include taxes?', 'wp-graphql-woocommerce' ),
					),
					'cartHash'              => array(
						'type'        => 'String',
						'description' => __( 'Cart hash', 'wp-graphql-woocommerce' ),
					),
					'customerNote'          => array(
						'type'        => 'String',
						'description' => __( 'Customer note', 'wp-graphql-woocommerce' ),
					),
					'isDownloadPermitted'   => array(
						'type'        => 'Boolean',
						'description' => __( 'Is product download is permitted', 'wp-graphql-woocommerce' ),
					),
					'billing'               => array(
						'type'        => 'CustomerAddress',
						'description' => __( 'Order billing properties', 'wp-graphql-woocommerce' ),
					),
					'shipping'              => array(
						'type'        => 'CustomerAddress',
						'description' => __( 'Order shipping properties', 'wp-graphql-woocommerce' ),
					),
					'status'                => array(
						'type'        => 'OrderStatusEnum',
						'description' => __( 'Order status', 'wp-graphql-woocommerce' ),
					),
					'parent'                => array(
						'type'        => 'Order',
						'description' => __( 'Parent order', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $order, array $args, AppContext $context ) {
							return Factory::resolve_crud_object( $order->parent_id, $context );
						},
					),
					'customer'              => array(
						'type'        => 'Customer',
						'description' => __( 'Order customer', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $order, array $args, AppContext $context ) {
							if ( empty( $order->customer_id ) ) {
								return Factory::resolve_session_customer();
							}

							return Factory::resolve_customer( $order->customer_id, $context );
						},
					),
					'shippingAddressMapUrl' => array(
						'type'        => 'String',
						'description' => __( 'Order customer', 'wp-graphql-woocommerce' ),
					),
					'hasBillingAddress'     => array(
						'type'        => 'Boolean',
						'description' => __( 'Order has a billing address?', 'wp-graphql-woocommerce' ),
					),
					'hasShippingAddress'    => array(
						'type'        => 'Boolean',
						'description' => __( 'Order has a shipping address?', 'wp-graphql-woocommerce' ),
					),
					'needsShippingAddress'  => array(
						'type'        => 'Boolean',
						'description' => __( 'If order needs shipping address', 'wp-graphql-woocommerce' ),
					),
					'hasDownloadableItem'   => array(
						'type'        => 'Boolean',
						'description' => __( 'If order contains a downloadable product', 'wp-graphql-woocommerce' ),
					),
					'needsPayment'          => array(
						'type'        => 'Boolean',
						'description' => __( 'If order needs payment', 'wp-graphql-woocommerce' ),
					),
					'needsProcessing'       => array(
						'type'        => 'Boolean',
						'description' => __( 'If order needs processing before it can be completed', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
