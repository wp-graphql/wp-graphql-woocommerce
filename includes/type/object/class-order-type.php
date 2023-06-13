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
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Data\Connection\Order_Item_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Connection\Downloadable_Item_Connection_Resolver;

/**
 * Class Order_Type
 */
class Order_Type {

	/**
	 * Register Order type and queries to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'Order',
			[
				'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [
					'Node',
					'NodeWithComments',
				],
				/**
				 * Allows for a decisive filtering of the order fields.
				 * Note: Only use if deregisteration or renaming the field(s) has failed.
				 *
				 * @param array $fields  Order field definitions.
				 * @return array
				 */
				'fields'      => apply_filters( 'woographql_order_field_definitions', self::get_fields() ),
				/**
				 * Allows for a decisive filtering of the order connections.
				 * Note: Only use if deregisteration or renaming the connection(s) has failed.
				 *
				 * @param array $connections  Order connection definitions.
				 * @return array
				 */
				'connections' => apply_filters( 'woographql_order_connection_definitions', self::get_connections() ),
			]
		);
	}

	/**
	 * Returns the "Order" type fields.
	 *
	 * @param array $other_fields Extra fields configs to be added or override the default field definitions.
	 * @return array
	 */
	public static function get_fields( $other_fields = [] ) {
		return array_merge(
			[
				'id'                    => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => __( 'The globally unique identifier for the order', 'wp-graphql-woocommerce' ),
				],
				'databaseId'            => [
					'type'        => 'Int',
					'description' => __( 'The ID of the order in the database', 'wp-graphql-woocommerce' ),
				],
				'orderKey'              => [
					'type'        => 'String',
					'description' => __( 'Order key', 'wp-graphql-woocommerce' ),
				],
				'date'                  => [
					'type'        => 'String',
					'description' => __( 'Date order was created', 'wp-graphql-woocommerce' ),
				],
				'modified'              => [
					'type'        => 'String',
					'description' => __( 'Date order was last updated', 'wp-graphql-woocommerce' ),
				],
				'currency'              => [
					'type'        => 'String',
					'description' => __( 'Order currency', 'wp-graphql-woocommerce' ),
				],
				'paymentMethod'         => [
					'type'        => 'String',
					'description' => __( 'Payment method', 'wp-graphql-woocommerce' ),
				],
				'paymentMethodTitle'    => [
					'type'        => 'String',
					'description' => __( 'Payment method title', 'wp-graphql-woocommerce' ),
				],
				'transactionId'         => [
					'type'        => 'String',
					'description' => __( 'Transaction ID', 'wp-graphql-woocommerce' ),
				],
				'customerIpAddress'     => [
					'type'        => 'String',
					'description' => __( 'Customer IP Address', 'wp-graphql-woocommerce' ),
				],
				'customerUserAgent'     => [
					'type'        => 'String',
					'description' => __( 'Customer User Agent', 'wp-graphql-woocommerce' ),
				],
				'createdVia'            => [
					'type'        => 'String',
					'description' => __( 'How order was created', 'wp-graphql-woocommerce' ),
				],
				'dateCompleted'         => [
					'type'        => 'String',
					'description' => __( 'Date order was completed', 'wp-graphql-woocommerce' ),
				],
				'datePaid'              => [
					'type'        => 'String',
					'description' => __( 'Date order was paid', 'wp-graphql-woocommerce' ),
				],
				'discountTotal'         => [
					'type'        => 'String',
					'description' => __( 'Discount total amount', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->discountTotalRaw;
						} else {
							// @codingStandardsIgnoreLine.
							return $source->discountTotal;
						}
					},
				],
				'discountTax'           => [
					'type'        => 'String',
					'description' => __( 'Discount tax amount', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->discountTaxRaw;
						} else {
							// @codingStandardsIgnoreLine.
							return $source->discountTax;
						}
					},
				],
				'shippingTotal'         => [
					'type'        => 'String',
					'description' => __( 'Shipping total amount', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->shippingTotalRaw;
						}

						// @codingStandardsIgnoreLine.
						return $source->shippingTotal;
					},
				],
				'shippingTax'           => [
					'type'        => 'String',
					'description' => __( 'Shipping tax amount', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->shippingTaxRaw;
						}

						// @codingStandardsIgnoreLine.
						return $source->shippingTax;
					},
				],
				'cartTax'               => [
					'type'        => 'String',
					'description' => __( 'Cart tax amount', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->cartTaxRaw;
						} else {
							// @codingStandardsIgnoreLine.
							return $source->cartTax;
						}
					},
				],
				'total'                 => [
					'type'        => 'String',
					'description' => __( 'Order grand total', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->totalRaw;
						} else {
							return $source->total;
						}
					},
				],
				'totalTax'              => [
					'type'        => 'String',
					'description' => __( 'Order taxes', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->totalTaxRaw;
						} else {
							// @codingStandardsIgnoreLine.
							return $source->totalTax;
						}
					},
				],
				'subtotal'              => [
					'type'        => 'String',
					'description' => __( 'Order subtotal', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => function( $source, $args ) {
						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							// @codingStandardsIgnoreLine.
							return $source->subtotalRaw;
						} else {
							return $source->subtotal;
						}
					},
				],
				'orderNumber'           => [
					'type'        => 'String',
					'description' => __( 'Order number', 'wp-graphql-woocommerce' ),
				],
				'orderVersion'          => [
					'type'        => 'String',
					'description' => __( 'Order version', 'wp-graphql-woocommerce' ),
				],
				'pricesIncludeTax'      => [
					'type'        => 'Boolean',
					'description' => __( 'Prices include taxes?', 'wp-graphql-woocommerce' ),
				],
				'cartHash'              => [
					'type'        => 'String',
					'description' => __( 'Cart hash', 'wp-graphql-woocommerce' ),
				],
				'customerNote'          => [
					'type'        => 'String',
					'description' => __( 'Customer note', 'wp-graphql-woocommerce' ),
				],
				'isDownloadPermitted'   => [
					'type'        => 'Boolean',
					'description' => __( 'Is product download is permitted', 'wp-graphql-woocommerce' ),
				],
				'billing'               => [
					'type'        => 'CustomerAddress',
					'description' => __( 'Order billing properties', 'wp-graphql-woocommerce' ),
				],
				'shipping'              => [
					'type'        => 'CustomerAddress',
					'description' => __( 'Order shipping properties', 'wp-graphql-woocommerce' ),
				],
				'status'                => [
					'type'        => 'OrderStatusEnum',
					'description' => __( 'Order status', 'wp-graphql-woocommerce' ),
				],
				'parent'                => [
					'type'        => 'Order',
					'description' => __( 'Parent order', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $order, array $args, AppContext $context ) {
						return Factory::resolve_crud_object( $order->parent_id, $context );
					},
				],
				'customer'              => [
					'type'        => 'Customer',
					'description' => __( 'Order customer', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $order, array $args, AppContext $context ) {
						if ( empty( $order->customer_id ) ) {
							// Guest orders don't have an attached customer.
							return null;
						}

						return Factory::resolve_customer( $order->customer_id, $context );
					},
				],
				'shippingAddressMapUrl' => [
					'type'        => 'String',
					'description' => __( 'Order customer', 'wp-graphql-woocommerce' ),
				],
				'hasBillingAddress'     => [
					'type'        => 'Boolean',
					'description' => __( 'Order has a billing address?', 'wp-graphql-woocommerce' ),
				],
				'hasShippingAddress'    => [
					'type'        => 'Boolean',
					'description' => __( 'Order has a shipping address?', 'wp-graphql-woocommerce' ),
				],
				'needsShippingAddress'  => [
					'type'        => 'Boolean',
					'description' => __( 'If order needs shipping address', 'wp-graphql-woocommerce' ),
				],
				'hasDownloadableItem'   => [
					'type'        => 'Boolean',
					'description' => __( 'If order contains a downloadable product', 'wp-graphql-woocommerce' ),
				],
				'needsPayment'          => [
					'type'        => 'Boolean',
					'description' => __( 'If order needs payment', 'wp-graphql-woocommerce' ),
				],
				'needsProcessing'       => [
					'type'        => 'Boolean',
					'description' => __( 'If order needs processing before it can be completed', 'wp-graphql-woocommerce' ),
				],
				'metaData'              => Meta_Data_Type::get_metadata_field_definition(),
			],
			$other_fields
		);
	}

	/**
	 * Returns the "Order" type connections.
	 *
	 * @param array $other_connections Extra connections configs to be added or override the default connection definitions.
	 * @return array
	 */
	public static function get_connections( $other_connections = [] ) {
		return array_merge(
			[
				'taxLines'          => [
					'toType'         => 'TaxLine',
					'connectionArgs' => [],
					'resolve'        => [ __CLASS__, 'resolve_item_connection' ],
				],
				'feeLines'          => [
					'toType'         => 'FeeLine',
					'connectionArgs' => [],
					'resolve'        => [ __CLASS__, 'resolve_item_connection' ],
				],
				'shippingLines'     => [
					'toType'         => 'ShippingLine',
					'connectionArgs' => [],
					'resolve'        => [ __CLASS__, 'resolve_item_connection' ],
				],
				'couponLines'       => [
					'toType'         => 'CouponLine',
					'connectionArgs' => [],
					'resolve'        => [ __CLASS__, 'resolve_item_connection' ],
				],
				'lineItems'         => [
					'toType'         => 'LineItem',
					'connectionArgs' => [],
					'resolve'        => [ __CLASS__, 'resolve_item_connection' ],
				],
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
	 * Order Item connection resolver callback
	 *
	 * @param \WPGraphQL\WooCommerce\Model\Order $source   Source order.
	 * @param array                              $args     Connection args.
	 * @param AppContext                         $context  AppContext instance.
	 * @param ResolveInfo                        $info     ResolveInfo instance.
	 *
	 * @return array
	 */
	public static function resolve_item_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Order_Item_Connection_Resolver( $source, $args, $context, $info );

		return $resolver->get_connection();
	}
}
