<?php
/**
 * WPInputObjectType - LineItemInput
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPInputObject;

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
					'productId'   => array(
						'type'        => array( 'non_null' => 'Int' ),
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
				),
			)
		);
	}
}
