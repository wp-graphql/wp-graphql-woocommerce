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
			array(
				'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array(
					'Node',
					'NodeWithComments',
				),
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
			)
		);
	}

	/**
	 * Returns the "Order" type fields.
	 *
	 * @param array $other_fields Extra fields configs to be added or override the default field definitions.
	 * @return array
	 */
	public static function get_fields( $other_fields = array() ) {
		return array_merge(
			array(
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $source, $args ) {
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
					'resolve'     => static function ( $order, array $args, AppContext $context ) {
						return Factory::resolve_crud_object( $order->parent_id, $context );
					},
				),
				'customer'              => array(
					'type'        => 'Customer',
					'description' => __( 'Order customer', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $order, array $args, AppContext $context ) {
						if ( empty( $order->customer_id ) ) {
							// Guest orders don't have an attached customer.
							return null;
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
				'metaData'              => Meta_Data_Type::get_metadata_field_definition(),
			),
			$other_fields
		);
	}

	/**
	 * Returns the "Order" type connections.
	 *
	 * @param array $other_connections Extra connections configs to be added or override the default connection definitions.
	 * @return array
	 */
	public static function get_connections( $other_connections = array() ) {
		return array_merge(
			array(
				'taxLines'          => array(
					'toType'         => 'TaxLine',
					'connectionArgs' => array(),
					'resolve'        => array( self::class, 'resolve_item_connection' ),
				),
				'feeLines'          => array(
					'toType'         => 'FeeLine',
					'connectionArgs' => array(),
					'resolve'        => array( self::class, 'resolve_item_connection' ),
				),
				'shippingLines'     => array(
					'toType'         => 'ShippingLine',
					'connectionArgs' => array(),
					'resolve'        => array( self::class, 'resolve_item_connection' ),
				),
				'couponLines'       => array(
					'toType'         => 'CouponLine',
					'connectionArgs' => array(),
					'resolve'        => array( self::class, 'resolve_item_connection' ),
				),
				'lineItems'         => array(
					'toType'         => 'LineItem',
					'connectionArgs' => array(),
					'resolve'        => array( self::class, 'resolve_item_connection' ),
				),
				'downloadableItems' => array(
					'toType'         => 'DownloadableItem',
					'connectionArgs' => array(
						'active'                => array(
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that can be downloaded now.', 'wp-graphql-woocommerce' ),
						),
						'expired'               => array(
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that are expired.', 'wp-graphql-woocommerce' ),
						),
						'hasDownloadsRemaining' => array(
							'type'        => 'Boolean',
							'description' => __( 'Limit results to downloadable items that have downloads remaining.', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Downloadable_Item_Connection_Resolver( $source, $args, $context, $info );

						return $resolver->get_connection();
					},
				),
			),
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
