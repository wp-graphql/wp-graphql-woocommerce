<?php
/**
 * WPInputObjectType - LineItemInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Line_Item_Input
 */
class Line_Item_Input {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'LineItemInput',
			array(
				'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'          => array(
						'type'        => 'ID',
						'description' => __( 'Line Item ID', 'wp-graphql-woocommerce' ),
					),
					'name'        => array(
						'type'        => 'String',
						'description' => __( 'Line name', 'wp-graphql-woocommerce' ),
					),
					'productId'   => array(
						'type'        => 'Int',
						'description' => __( 'Product ID.', 'wp-graphql-woocommerce' ),
					),
					'variationId' => array(
						'type'        => 'Int',
						'description' => __( 'Variation ID, if applicable.', 'wp-graphql-woocommerce' ),
					),
					'quantity'    => array(
						'type'        => 'Int',
						'description' => __( 'Quantity ordered.', 'wp-graphql-woocommerce' ),
					),
					'taxClass'    => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Tax class of product.', 'wp-graphql-woocommerce' ),
					),
					'subtotal'    => array(
						'type'        => 'String',
						'description' => __( 'Line subtotal (before discounts).', 'wp-graphql-woocommerce' ),
					),
					'total'       => array(
						'type'        => 'String',
						'description' => __( 'Line total (after discounts).', 'wp-graphql-woocommerce' ),
					),
					'metaData'    => array(
						'type'        => array( 'list_of' => 'MetaDataInput' ),
						'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
					),
					'sku'         => array(
						'type'        => 'string',
						'description' => __( 'Product SKU.', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
