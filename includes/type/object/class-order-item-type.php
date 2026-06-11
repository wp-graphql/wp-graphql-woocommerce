<?php
/**
 * WPObject Type - Order_Item_Type
 *
 * Registers OrderItem WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Order_Item_Type
 */
class Order_Item_Type {
	/**
	 * Register order item type
	 *
	 * @return void
	 */
	public static function register() {
		$types = [
			'CouponLine'   => [
				// Description.
				__( 'a coupon line object', 'graphql-for-ecommerce' ),
				// Fields.
				[
					'code'        => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line\'s Coupon code', 'graphql-for-ecommerce' );
						},
					],
					'discount'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line\'s Discount total', 'graphql-for-ecommerce' );
						},
					],
					'discountTax' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line\'s Discount total tax', 'graphql-for-ecommerce' );
						},
					],
					'coupon'      => [
						'type'        => 'Coupon',
						'description' => static function () {
							return __( 'Line\'s Coupon', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source, array $args, AppContext $context ) {
							return Factory::resolve_crud_object( $source->coupon_id, $context );
						},
					],
				],
			],
			'FeeLine'      => [
				// Description.
				__( 'a fee line object', 'graphql-for-ecommerce' ),
				// Fields.
				[
					'amount'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Fee amount', 'graphql-for-ecommerce' );
						},
					],
					'name'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Fee name', 'graphql-for-ecommerce' );
						},
					],
					'taxStatus' => [
						'type'        => 'TaxStatusEnum',
						'description' => static function () {
							return __( 'Tax status of fee', 'graphql-for-ecommerce' );
						},
					],
					'total'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line total (after discounts)', 'graphql-for-ecommerce' );
						},
					],
					'totalTax'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line total tax (after discounts)', 'graphql-for-ecommerce' );
						},
					],
					'taxes'     => [
						'type'        => [ 'list_of' => 'OrderItemTax' ],
						'description' => static function () {
							return __( 'Line taxes', 'graphql-for-ecommerce' );
						},
					],
					'taxClass'  => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Line tax class', 'graphql-for-ecommerce' );
						},
					],
				],
			],
			'ShippingLine' => [
				// Description.
				__( 'a shipping line object', 'graphql-for-ecommerce' ),
				// Fields.
				[
					'methodTitle'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Shipping Line\'s shipping method name', 'graphql-for-ecommerce' );
						},
					],
					'total'          => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line total (after discounts)', 'graphql-for-ecommerce' );
						},
					],
					'totalTax'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line total tax (after discounts)', 'graphql-for-ecommerce' );
						},
					],
					'taxes'          => [
						'type'        => [ 'list_of' => 'OrderItemTax' ],
						'description' => static function () {
							return __( 'Line taxes', 'graphql-for-ecommerce' );
						},
					],
					'taxClass'       => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Line tax class', 'graphql-for-ecommerce' );
						},
					],
					'shippingMethod' => [
						'type'        => 'ShippingMethod',
						'description' => static function () {
							return __( 'Shipping Line\'s shipping method', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return Factory::resolve_shipping_method( $source->method_id );
						},
					],
				],
			],
			'TaxLine'      => [
				// Description.
				__( 'a tax line object', 'graphql-for-ecommerce' ),
				// Fields.
				[
					'rateCode'         => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax rate code/name', 'graphql-for-ecommerce' );
						},
					],
					'label'            => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax rate label', 'graphql-for-ecommerce' );
						},
					],
					'taxTotal'         => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax total (not including shipping taxes)', 'graphql-for-ecommerce' );
						},
					],
					'shippingTaxTotal' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Tax line\'s shipping tax total', 'graphql-for-ecommerce' );
						},
					],
					'isCompound'       => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Is this a compound tax rate?', 'graphql-for-ecommerce' );
						},
					],
					'taxRate'          => [
						'type'        => 'TaxRate',
						'description' => static function () {
							return __( 'Tax line\'s tax rate', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source, array $args, AppContext $context ) {
							return Factory::resolve_tax_rate( $source->rate_id, $context );
						},
					],
				],
			],
			'LineItem'     => [
				// Description.
				__( 'a line item object', 'graphql-for-ecommerce' ),
				// Fields.
				[
					'productId'     => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Line item\'s product ID', 'graphql-for-ecommerce' );
						},
					],
					'variationId'   => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Line item\'s product variation ID', 'graphql-for-ecommerce' );
						},
					],
					'quantity'      => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Line item\'s product quantity', 'graphql-for-ecommerce' );
						},
					],
					'taxClass'      => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Line item\'s tax class', 'graphql-for-ecommerce' );
						},
					],
					'subtotal'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line item\'s subtotal', 'graphql-for-ecommerce' );
						},
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => static function () {
									return __( 'Format of the price', 'graphql-for-ecommerce' );
								},
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $source->subtotal;
							}
							return ! empty( $source->subtotal ) ? \wc_graphql_price( $source->subtotal ) : null;
						},
					],
					'subtotalTax'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line item\'s subtotal tax', 'graphql-for-ecommerce' );
						},
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => static function () {
									return __( 'Format of the price', 'graphql-for-ecommerce' );
								},
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $source->subtotalTax;
							}
							return ! empty( $source->subtotalTax ) ? \wc_graphql_price( $source->subtotalTax ) : null;
						},
					],
					'total'         => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line item\'s total', 'graphql-for-ecommerce' );
						},
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => static function () {
									return __( 'Format of the price', 'graphql-for-ecommerce' );
								},
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $source->total;
							}
							return ! empty( $source->total ) ? \wc_graphql_price( $source->total ) : null;
						},
					],
					'totalTax'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line item\'s total tax', 'graphql-for-ecommerce' );
						},
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => static function () {
									return __( 'Format of the price', 'graphql-for-ecommerce' );
								},
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $source->totalTax;
							}
							return ! empty( $source->totalTax ) ? \wc_graphql_price( $source->totalTax ) : null;
						},
					],
					'taxes'         => [
						'type'        => [ 'list_of' => 'OrderItemTax' ],
						'description' => static function () {
							return __( 'Line item\'s taxes', 'graphql-for-ecommerce' );
						},
					],
					'itemDownloads' => [
						'type'        => [ 'list_of' => 'ProductDownload' ],
						'description' => static function () {
							return __( 'Line item\'s taxes', 'graphql-for-ecommerce' );
						},
					],
					'taxStatus'     => [
						'type'        => 'TaxStatusEnum',
						'description' => static function () {
							return __( 'Line item\'s taxes', 'graphql-for-ecommerce' );
						},
					],
				],
				// Connections.
				[
					'product'   => [
						'toType'   => 'Product',
						'oneToOne' => true,
						'resolve'  => static function ( $source, array $args, AppContext $context, $info ) {
							$id       = $source->productId; // @phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

							return $resolver
								->one_to_one()
								->set_query_arg( 'p', $id )
								->get_connection();
						},
					],
					'variation' => [
						'toType'   => 'ProductVariation',
						'oneToOne' => true,
						'resolve'  => static function ( $source, array $args, AppContext $context, $info ) {
							$id       = $source->variationId; // @phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product_variation' );

							if ( ! $id ) {
								return null;
							}

							return $resolver
								->one_to_one()
								->set_query_arg( 'p', $id )
								->get_connection();
						},
					],
				],
			],
		];

		// Registers order item objects.
		foreach ( $types as $type_name => $config ) {
			register_graphql_object_type(
				$type_name,
				[
					'description' => $config[0],
					'fields'      => self::get_fields( $config[1] ),
					'connections' => ! empty( $config[2] ) ? $config[2] : null,
					'interfaces'  => [ 'Node' ],
				]
			);
		}

		// Registers tax statement object.
		register_graphql_object_type(
			'OrderItemTax',
			[
				'description' => static function () {
					return __( 'Order item tax statement', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'taxLineId' => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => static function () {
							return __( 'Order item ID for tax line connected to this statement', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['ID'] ) ? $source['ID'] : null;
						},
					],
					'subtotal'  => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Subtotal', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['subtotal'] ) ? $source['subtotal'] : null;
						},
					],
					'total'     => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Total', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['total'] ) ? $source['total'] : null;
						},
					],
					'amount'    => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Amount taxed', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['amount'] ) ? $source['amount'] : null;
						},
					],
					'taxLine'   => [
						'type'        => 'TaxLine',
						'description' => static function () {
							return __( 'Tax line connected to this statement', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							$item = \WC_Order_Factory::get_order_item( $source['ID'] );
							// Return early if the item is not found.
							if ( false === $item ) {
								return null;
							}

							return Factory::resolve_order_item( $item );
						},
					],
				],
			]
		);
	}

	/**
	 * Returns type fields definition
	 *
	 * @param array $fields - type specific fields.
	 * @return array
	 */
	private static function get_fields( $fields = [] ) {
		return array_merge(
			[
				'id'         => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => static function () {
						return __( 'The ID of the order item in the database', 'graphql-for-ecommerce' );
					},
				],
				'databaseId' => [
					'type'        => 'Int',
					'description' => static function () {
						return __( 'The ID of the order item in the database', 'graphql-for-ecommerce' );
					},
				],
				'orderId'    => [
					'type'        => 'Int',
					'description' => static function () {
						return __( 'The Id of the order the order item belongs to.', 'graphql-for-ecommerce' );
					},
				],

				'metaData'   => Meta_Data_Type::get_metadata_field_definition(),
			],
			$fields
		);
	}
}
