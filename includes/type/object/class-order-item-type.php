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
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

/**
 * Class Order_Item_Type
 */
class Order_Item_Type {

	/**
	 * Register order item type
	 */
	public static function register() {
		$types = array(
			'CouponLine'   => array(
				// Description.
				__( 'a coupon line object', 'wp-graphql-woocommerce' ),
				// Fields.
				array(
					'code'        => array(
						'type'        => 'String',
						'description' => __( 'Line\'s Coupon code', 'wp-graphql-woocommerce' ),
					),
					'discount'    => array(
						'type'        => 'String',
						'description' => __( 'Line\'s Discount total', 'wp-graphql-woocommerce' ),
					),
					'discountTax' => array(
						'type'        => 'String',
						'description' => __( 'Line\'s Discount total tax', 'wp-graphql-woocommerce' ),
					),
					'coupon'      => array(
						'type'        => 'Coupon',
						'description' => __( 'Line\'s Coupon', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							return Factory::resolve_crud_object( $source->coupon_id, $context );
						},
					),
				),
			),
			'FeeLine'      => array(
				// Description.
				__( 'a fee line object', 'wp-graphql-woocommerce' ),
				// Fields.
				array(
					'amount'    => array(
						'type'        => 'String',
						'description' => __( 'Fee amount', 'wp-graphql-woocommerce' ),
					),
					'name'      => array(
						'type'        => 'String',
						'description' => __( 'Fee name', 'wp-graphql-woocommerce' ),
					),
					'taxStatus' => array(
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Tax status of fee', 'wp-graphql-woocommerce' ),
					),
					'total'     => array(
						'type'        => 'String',
						'description' => __( 'Line total (after discounts)', 'wp-graphql-woocommerce' ),
					),
					'totalTax'  => array(
						'type'        => 'String',
						'description' => __( 'Line total tax (after discounts)', 'wp-graphql-woocommerce' ),
					),
					'taxes'     => array(
						'type'        => array( 'list_of' => 'OrderItemTax' ),
						'description' => __( 'Line taxes', 'wp-graphql-woocommerce' ),
					),
					'taxClass'  => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Line tax class', 'wp-graphql-woocommerce' ),
					),
				),
			),
			'ShippingLine' => array(
				// Description.
				__( 'a shipping line object', 'wp-graphql-woocommerce' ),
				// Fields.
				array(
					'methodTitle'    => array(
						'type'        => 'String',
						'description' => __( 'Shipping Line\'s shipping method name', 'wp-graphql-woocommerce' ),
					),
					'total'          => array(
						'type'        => 'String',
						'description' => __( 'Line total (after discounts)', 'wp-graphql-woocommerce' ),
					),
					'totalTax'       => array(
						'type'        => 'String',
						'description' => __( 'Line total tax (after discounts)', 'wp-graphql-woocommerce' ),
					),
					'taxes'          => array(
						'type'        => array( 'list_of' => 'OrderItemTax' ),
						'description' => __( 'Line taxes', 'wp-graphql-woocommerce' ),
					),
					'taxClass'       => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Line tax class', 'wp-graphql-woocommerce' ),
					),
					'shippingMethod' => array(
						'type'        => 'ShippingMethod',
						'description' => __( 'Shipping Line\'s shipping method', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return Factory::resolve_shipping_method( $source->method_id );
						},
					),
				),
			),
			'TaxLine'      => array(
				// Description.
				__( 'a tax line object', 'wp-graphql-woocommerce' ),
				// Fields.
				array(
					'rateCode'         => array(
						'type'        => 'String',
						'description' => __( 'Tax rate code/name', 'wp-graphql-woocommerce' ),
					),
					'label'            => array(
						'type'        => 'String',
						'description' => __( 'Tax rate label', 'wp-graphql-woocommerce' ),
					),
					'taxTotal'         => array(
						'type'        => 'String',
						'description' => __( 'Tax total (not including shipping taxes)', 'wp-graphql-woocommerce' ),
					),
					'shippingTaxTotal' => array(
						'type'        => 'String',
						'description' => __( 'Tax line\'s shipping tax total', 'wp-graphql-woocommerce' ),
					),
					'isCompound'       => array(
						'type'        => 'Boolean',
						'description' => __( 'Is this a compound tax rate?', 'wp-graphql-woocommerce' ),
					),
					'taxRate'          => array(
						'type'        => 'TaxRate',
						'description' => __( 'Tax line\'s tax rate', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							return Factory::resolve_tax_rate( $source->rate_id, $context );
						},
					),
				),
			),
			'LineItem'     => array(
				// Description.
				__( 'a line item object', 'wp-graphql-woocommerce' ),
				// Fields.
				array(
					'productId'     => array(
						'type'        => 'Int',
						'description' => __( 'Line item\'s product ID', 'wp-graphql-woocommerce' ),
					),
					'variationId'   => array(
						'type'        => 'Int',
						'description' => __( 'Line item\'s product variation ID', 'wp-graphql-woocommerce' ),
					),
					'quantity'      => array(
						'type'        => 'Int',
						'description' => __( 'Line item\'s product quantity', 'wp-graphql-woocommerce' ),
					),
					'taxClass'      => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Line item\'s tax class', 'wp-graphql-woocommerce' ),
					),
					'subtotal'      => array(
						'type'        => 'String',
						'description' => __( 'Line item\'s subtotal', 'wp-graphql-woocommerce' ),
					),
					'subtotalTax'   => array(
						'type'        => 'String',
						'description' => __( 'Line item\'s subtotal tax', 'wp-graphql-woocommerce' ),
					),
					'total'         => array(
						'type'        => 'String',
						'description' => __( 'Line item\'s total', 'wp-graphql-woocommerce' ),
					),
					'totalTax'      => array(
						'type'        => 'String',
						'description' => __( 'Line item\'s total tax', 'wp-graphql-woocommerce' ),
					),
					'taxes'         => array(
						'type'        => array( 'list_of' => 'OrderItemTax' ),
						'description' => __( 'Line item\'s taxes', 'wp-graphql-woocommerce' ),
					),
					'itemDownloads' => array(
						'type'        => array( 'list_of' => 'ProductDownload' ),
						'description' => __( 'Line item\'s taxes', 'wp-graphql-woocommerce' ),
					),
					'taxStatus'     => array(
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Line item\'s taxes', 'wp-graphql-woocommerce' ),
					),
				),
				// Connections.
				array(
					'product'   => array(
						'toType'     => 'Product',
						'oneToOne'   => true,
						'resolve'    => function ( $source, array $args, AppContext $context, $info ) {
							$id       = $source->productId;
							$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );

							return $resolver
								->one_to_one()
								->set_query_arg( 'p', $id )
								->get_connection();
						},
					),
					'variation' => array(
						'toType'     => 'ProductVariation',
						'oneToOne'   => true,
						'resolve'    => function ( $source, array $args, AppContext $context, $info ) {
							$id       = $source->variationId;
							$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product_variation' );

							if ( ! $id ) {
								return null;
							}

							return $resolver
								->one_to_one()
								->set_query_arg( 'p', $id )
								->get_connection();
						},
					),
				),
			),
		);

		// Registers order item objects.
		foreach ( $types as $type_name => $config ) {
			register_graphql_object_type(
				$type_name,
				array(
					'description' => $config[0],
					'fields'      => self::get_fields( $config[1] ),
					'connections' => ! empty( $config[2] ) ? $config[2] : null,
				)
			);
		}

		// Registers tax statement object.
		register_graphql_object_type(
			'OrderItemTax',
			array(
				'description' => __( 'Order item tax statement', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'taxLineId' => array(
						'type'        => array( 'non_null' => 'Int' ),
						'description' => __( 'Order item ID for tax line connected to this statement', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['ID'] ) ? $source['ID'] : null;
						},
					),
					'subtotal'  => array(
						'type'        => 'Float',
						'description' => __( 'Subtotal', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['subtotal'] ) ? $source['subtotal'] : null;
						},
					),
					'total'     => array(
						'type'        => 'Float',
						'description' => __( 'Total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['total'] ) ? $source['total'] : null;
						},
					),
					'amount'    => array(
						'type'        => 'Float',
						'description' => __( 'Amount taxed', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['amount'] ) ? $source['amount'] : null;
						},
					),
					'taxLine'   => array(
						'type'        => 'TaxLine',
						'description' => __( 'Tax line connected to this statement', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							$item               = \WC_Order_Factory::get_order_item( $source['ID'] );
							$item->cached_order = $source;
							return ! empty( $item ) ? Factory::resolve_order_item( $item ) : null;
						},
					),
				),
			)
		);
	}

	/**
	 * Returns type fields definition
	 *
	 * @param array $fields - type specific fields.
	 * @return array
	 */
	private static function get_fields( $fields = array() ) {
		return array_merge(
			array(
				'databaseId' => array(
					'type'        => 'Int',
					'description' => __( 'The ID of the order item in the database', 'wp-graphql-woocommerce' ),
				),
				'orderId'    => array(
					'type'        => 'Int',
					'description' => __( 'The Id of the order the order item belongs to.', 'wp-graphql-woocommerce' ),
				),

				'metaData'   => Meta_Data_Type::get_metadata_field_definition(),
			),
			$fields
		);
	}
}
