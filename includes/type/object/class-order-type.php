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

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Downloadable_Item_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Connection\Order_Item_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Factory;

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
				'description' => static function () {
					return __( 'A order object', 'graphql-for-ecommerce' );
				},
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
					'description' => static function () {
						return __( 'The globally unique identifier for the order', 'graphql-for-ecommerce' );
					},
				],
				'databaseId'            => [
					'type'        => 'Int',
					'description' => static function () {
						return __( 'The ID of the order in the database', 'graphql-for-ecommerce' );
					},
				],
				'orderKey'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Order key', 'graphql-for-ecommerce' );
					},
				],
				'date'                  => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Date order was created', 'graphql-for-ecommerce' );
					},
				],
				'modified'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Date order was last updated', 'graphql-for-ecommerce' );
					},
				],
				'currency'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Order currency', 'graphql-for-ecommerce' );
					},
				],
				'paymentMethod'         => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Payment method', 'graphql-for-ecommerce' );
					},
				],
				'paymentMethodTitle'    => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Payment method title', 'graphql-for-ecommerce' );
					},
				],
				'transactionId'         => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Transaction ID', 'graphql-for-ecommerce' );
					},
				],
				'customerIpAddress'     => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Customer IP Address', 'graphql-for-ecommerce' );
					},
				],
				'customerUserAgent'     => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Customer User Agent', 'graphql-for-ecommerce' );
					},
				],
				'createdVia'            => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'How order was created', 'graphql-for-ecommerce' );
					},
				],
				'dateCompleted'         => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Date order was completed', 'graphql-for-ecommerce' );
					},
				],
				'datePaid'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Date order was paid', 'graphql-for-ecommerce' );
					},
				],
				'discountTotal'         => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Discount total amount', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Discount tax amount', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Shipping total amount', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Shipping tax amount', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Cart tax amount', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Order grand total', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Order taxes', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Order subtotal', 'graphql-for-ecommerce' );
					},
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => static function () {
								return __( 'Format of the price', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'     => static function ( $source, $args ) {
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
					'description' => static function () {
						return __( 'Order number', 'graphql-for-ecommerce' );
					},
				],
				'orderVersion'          => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Order version', 'graphql-for-ecommerce' );
					},
				],
				'pricesIncludeTax'      => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Prices include taxes?', 'graphql-for-ecommerce' );
					},
				],
				'cartHash'              => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Cart hash', 'graphql-for-ecommerce' );
					},
				],
				'customerNote'          => [
					'type'             => 'String',
					'description'      => static function () {
						return __( 'Customer note', 'graphql-for-ecommerce' );
					},
					'deprecatedReason' => __( 'Use "orderNotes" field instead.', 'graphql-for-ecommerce' ),
				],
				'isDownloadPermitted'   => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Is product download is permitted', 'graphql-for-ecommerce' );
					},
				],
				'billing'               => [
					'type'        => 'CustomerAddress',
					'description' => static function () {
						return __( 'Order billing properties', 'graphql-for-ecommerce' );
					},
				],
				'shipping'              => [
					'type'        => 'CustomerAddress',
					'description' => static function () {
						return __( 'Order shipping properties', 'graphql-for-ecommerce' );
					},
				],
				'status'                => [
					'type'        => 'OrderStatusEnum',
					'description' => static function () {
						return __( 'Order status', 'graphql-for-ecommerce' );
					},
				],
				'parent'                => [
					'type'        => 'Order',
					'description' => static function () {
						return __( 'Parent order', 'graphql-for-ecommerce' );
					},
					'resolve'     => static function ( $order, array $args, AppContext $context ) {
						return Factory::resolve_crud_object( $order->parent_id, $context );
					},
				],
				'customer'              => [
					'type'        => 'Customer',
					'description' => static function () {
						return __( 'Order customer', 'graphql-for-ecommerce' );
					},
					'resolve'     => static function ( $order, array $args, AppContext $context ) {
						if ( empty( $order->customer_id ) ) {
							// Guest orders don't have an attached customer.
							return null;
						}

						return Factory::resolve_customer( $order->customer_id, $context );
					},
				],
				'shippingAddressMapUrl' => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Order customer', 'graphql-for-ecommerce' );
					},
				],
				'hasBillingAddress'     => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Order has a billing address?', 'graphql-for-ecommerce' );
					},
				],
				'hasShippingAddress'    => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Order has a shipping address?', 'graphql-for-ecommerce' );
					},
				],
				'needsShippingAddress'  => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'If order needs shipping address', 'graphql-for-ecommerce' );
					},
				],
				'hasDownloadableItem'   => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'If order contains a downloadable product', 'graphql-for-ecommerce' );
					},
				],
				'needsPayment'          => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'If order needs payment', 'graphql-for-ecommerce' );
					},
				],
				'needsProcessing'       => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'If order needs processing before it can be completed', 'graphql-for-ecommerce' );
					},
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
					'resolve'        => [ self::class, 'resolve_item_connection' ],
				],
				'feeLines'          => [
					'toType'         => 'FeeLine',
					'connectionArgs' => [],
					'resolve'        => [ self::class, 'resolve_item_connection' ],
				],
				'shippingLines'     => [
					'toType'         => 'ShippingLine',
					'connectionArgs' => [],
					'resolve'        => [ self::class, 'resolve_item_connection' ],
				],
				'couponLines'       => [
					'toType'         => 'CouponLine',
					'connectionArgs' => [],
					'resolve'        => [ self::class, 'resolve_item_connection' ],
				],
				'lineItems'         => [
					'toType'         => 'LineItem',
					'connectionArgs' => [],
					'resolve'        => [ self::class, 'resolve_item_connection' ],
				],
				'downloadableItems' => [
					'toType'         => 'DownloadableItem',
					'connectionArgs' => [
						'active'                => [
							'type'        => 'Boolean',
							'description' => static function () {
								return __( 'Limit results to downloadable items that can be downloaded now.', 'graphql-for-ecommerce' );
							},
						],
						'expired'               => [
							'type'        => 'Boolean',
							'description' => static function () {
								return __( 'Limit results to downloadable items that are expired.', 'graphql-for-ecommerce' );
							},
						],
						'hasDownloadsRemaining' => [
							'type'        => 'Boolean',
							'description' => static function () {
								return __( 'Limit results to downloadable items that have downloads remaining.', 'graphql-for-ecommerce' );
							},
						],
					],
					'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
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
	 * @param \WPGraphQL\WooCommerce\Model\Order   $source   Source order.
	 * @param array                                $args     Connection args.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance.
	 *
	 * @return \GraphQL\Deferred
	 */
	public static function resolve_item_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Order_Item_Connection_Resolver( $source, $args, $context, $info );

		return $resolver->get_connection();
	}
}
