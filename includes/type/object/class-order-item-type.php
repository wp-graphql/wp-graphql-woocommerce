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
				__( 'a coupon line object', 'wp-graphql-woocommerce' ),
				// Fields.
				[
					'code'        => [
						'type'        => 'String',
						'description' => __( 'Line\'s Coupon code', 'wp-graphql-woocommerce' ),
					],
					'discount'    => [
						'type'        => 'String',
						'description' => __( 'Line\'s Discount total', 'wp-graphql-woocommerce' ),
					],
					'discountTax' => [
						'type'        => 'String',
						'description' => __( 'Line\'s Discount total tax', 'wp-graphql-woocommerce' ),
					],
					'coupon'      => [
						'type'        => 'Coupon',
						'description' => __( 'Line\'s Coupon', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, AppContext $context ) {
							return Factory::resolve_crud_object( $source->coupon_id, $context );
						},
					],
				],
			],
			'FeeLine'      => [
				// Description.
				__( 'a fee line object', 'wp-graphql-woocommerce' ),
				// Fields.
				[
					'amount'    => [
						'type'        => 'String',
						'description' => __( 'Fee amount', 'wp-graphql-woocommerce' ),
					],
					'name'      => [
						'type'        => 'String',
						'description' => __( 'Fee name', 'wp-graphql-woocommerce' ),
					],
					'taxStatus' => [
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Tax status of fee', 'wp-graphql-woocommerce' ),
					],
					'total'     => [
						'type'        => 'String',
						'description' => __( 'Line total (after discounts)', 'wp-graphql-woocommerce' ),
					],
					'totalTax'  => [
						'type'        => 'String',
						'description' => __( 'Line total tax (after discounts)', 'wp-graphql-woocommerce' ),
					],
					'taxes'     => [
						'type'        => [ 'list_of' => 'OrderItemTax' ],
						'description' => __( 'Line taxes', 'wp-graphql-woocommerce' ),
					],
					'taxClass'  => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'Line tax class', 'wp-graphql-woocommerce' ),
					],
				],
			],
			'ShippingLine' => [
				// Description.
				__( 'a shipping line object', 'wp-graphql-woocommerce' ),
				// Fields.
				[
					'methodTitle'    => [
						'type'        => 'String',
						'description' => __( 'Shipping Line\'s shipping method name', 'wp-graphql-woocommerce' ),
					],
					'total'          => [
						'type'        => 'String',
						'description' => __( 'Line total (after discounts)', 'wp-graphql-woocommerce' ),
					],
					'totalTax'       => [
						'type'        => 'String',
						'description' => __( 'Line total tax (after discounts)', 'wp-graphql-woocommerce' ),
					],
					'taxes'          => [
						'type'        => [ 'list_of' => 'OrderItemTax' ],
						'description' => __( 'Line taxes', 'wp-graphql-woocommerce' ),
					],
					'taxClass'       => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'Line tax class', 'wp-graphql-woocommerce' ),
					],
					'shippingMethod' => [
						'type'        => 'ShippingMethod',
						'description' => __( 'Shipping Line\'s shipping method', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return Factory::resolve_shipping_method( $source->method_id );
						},
					],
				],
			],
			'TaxLine'      => [
				// Description.
				__( 'a tax line object', 'wp-graphql-woocommerce' ),
				// Fields.
				[
					'rateCode'         => [
						'type'        => 'String',
						'description' => __( 'Tax rate code/name', 'wp-graphql-woocommerce' ),
					],
					'label'            => [
						'type'        => 'String',
						'description' => __( 'Tax rate label', 'wp-graphql-woocommerce' ),
					],
					'taxTotal'         => [
						'type'        => 'String',
						'description' => __( 'Tax total (not including shipping taxes)', 'wp-graphql-woocommerce' ),
					],
					'shippingTaxTotal' => [
						'type'        => 'String',
						'description' => __( 'Tax line\'s shipping tax total', 'wp-graphql-woocommerce' ),
					],
					'isCompound'       => [
						'type'        => 'Boolean',
						'description' => __( 'Is this a compound tax rate?', 'wp-graphql-woocommerce' ),
					],
					'taxRate'          => [
						'type'        => 'TaxRate',
						'description' => __( 'Tax line\'s tax rate', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, AppContext $context ) {
							return Factory::resolve_tax_rate( $source->rate_id, $context );
						},
					],
				],
			],
			'LineItem'     => [
				// Description.
				__( 'a line item object', 'wp-graphql-woocommerce' ),
				// Fields.
				[
					'productId'     => [
						'type'        => 'Int',
						'description' => __( 'Line item\'s product ID', 'wp-graphql-woocommerce' ),
					],
					'variationId'   => [
						'type'        => 'Int',
						'description' => __( 'Line item\'s product variation ID', 'wp-graphql-woocommerce' ),
					],
					'quantity'      => [
						'type'        => 'Int',
						'description' => __( 'Line item\'s product quantity', 'wp-graphql-woocommerce' ),
					],
					'taxClass'      => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'Line item\'s tax class', 'wp-graphql-woocommerce' ),
					],
					'subtotal'      => [
						'type'        => 'String',
						'description' => __( 'Line item\'s subtotal', 'wp-graphql-woocommerce' ),
					],
					'subtotalTax'   => [
						'type'        => 'String',
						'description' => __( 'Line item\'s subtotal tax', 'wp-graphql-woocommerce' ),
					],
					'total'         => [
						'type'        => 'String',
						'description' => __( 'Line item\'s total', 'wp-graphql-woocommerce' ),
					],
					'totalTax'      => [
						'type'        => 'String',
						'description' => __( 'Line item\'s total tax', 'wp-graphql-woocommerce' ),
					],
					'taxes'         => [
						'type'        => [ 'list_of' => 'OrderItemTax' ],
						'description' => __( 'Line item\'s taxes', 'wp-graphql-woocommerce' ),
					],
					'itemDownloads' => [
						'type'        => [ 'list_of' => 'ProductDownload' ],
						'description' => __( 'Line item\'s taxes', 'wp-graphql-woocommerce' ),
					],
					'taxStatus'     => [
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Line item\'s taxes', 'wp-graphql-woocommerce' ),
					],
				],
				// Connections.
				[
					'product'   => [
						'toType'   => 'Product',
						'oneToOne' => true,
						'resolve'  => static function ( $source, array $args, AppContext $context, $info ) {
							$id       = $source->productId; // @phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );

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
				'description' => __( 'Order item tax statement', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'taxLineId' => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => __( 'Order item ID for tax line connected to this statement', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['ID'] ) ? $source['ID'] : null;
						},
					],
					'subtotal'  => [
						'type'        => 'Float',
						'description' => __( 'Subtotal', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['subtotal'] ) ? $source['subtotal'] : null;
						},
					],
					'total'     => [
						'type'        => 'Float',
						'description' => __( 'Total', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['total'] ) ? $source['total'] : null;
						},
					],
					'amount'    => [
						'type'        => 'Float',
						'description' => __( 'Amount taxed', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['amount'] ) ? $source['amount'] : null;
						},
					],
					'taxLine'   => [
						'type'        => 'TaxLine',
						'description' => __( 'Tax line connected to this statement', 'wp-graphql-woocommerce' ),
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
					'description' => __( 'The ID of the order item in the database', 'wp-graphql-woocommerce' ),
				],
				'databaseId' => [
					'type'        => 'Int',
					'description' => __( 'The ID of the order item in the database', 'wp-graphql-woocommerce' ),
				],
				'orderId'    => [
					'type'        => 'Int',
					'description' => __( 'The Id of the order the order item belongs to.', 'wp-graphql-woocommerce' ),
				],

				'metaData'   => Meta_Data_Type::get_metadata_field_definition(),
			],
			$fields
		);
	}
}
