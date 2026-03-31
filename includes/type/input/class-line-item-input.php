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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'LineItemInput',
			[
				'description' => static function () {
					return __( 'Meta data.', 'wp-graphql-woocommerce' );
				},
				'fields'      => [
					'id'          => [
						'type'        => 'ID',
						'description' => static function () {
					return __( 'Line Item ID', 'wp-graphql-woocommerce' );
				},
					],
					'name'        => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Line name', 'wp-graphql-woocommerce' );
				},
					],
					'productId'   => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'Product ID.', 'wp-graphql-woocommerce' );
				},
					],
					'variationId' => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'Variation ID, if applicable.', 'wp-graphql-woocommerce' );
				},
					],
					'quantity'    => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'Quantity ordered.', 'wp-graphql-woocommerce' );
				},
					],
					'taxClass'    => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
					return __( 'Tax class of product.', 'wp-graphql-woocommerce' );
				},
					],
					'subtotal'    => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Line subtotal (before discounts).', 'wp-graphql-woocommerce' );
				},
					],
					'total'       => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Line total (after discounts).', 'wp-graphql-woocommerce' );
				},
					],
					'metaData'    => [
						'type'        => [ 'list_of' => 'MetaDataInput' ],
						'description' => static function () {
					return __( 'Meta data.', 'wp-graphql-woocommerce' );
				},
					],
					'sku'         => [
						'type'        => 'string',
						'description' => static function () {
					return __( 'Product SKU.', 'wp-graphql-woocommerce' );
				},
					],
				],
			]
		);
	}
}
