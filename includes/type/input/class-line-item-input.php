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
					return __( 'Meta data.', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'          => [
						'type'        => 'ID',
						'description' => static function () {
							return __( 'Line Item ID', 'graphql-for-ecommerce' );
						},
					],
					'name'        => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line name', 'graphql-for-ecommerce' );
						},
					],
					'productId'   => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Product ID.', 'graphql-for-ecommerce' );
						},
					],
					'variationId' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Variation ID, if applicable.', 'graphql-for-ecommerce' );
						},
					],
					'quantity'    => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Quantity ordered.', 'graphql-for-ecommerce' );
						},
					],
					'taxClass'    => [
						'type'        => 'TaxClassEnum',
						'description' => static function () {
							return __( 'Tax class of product.', 'graphql-for-ecommerce' );
						},
					],
					'subtotal'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line subtotal (before discounts).', 'graphql-for-ecommerce' );
						},
					],
					'total'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Line total (after discounts).', 'graphql-for-ecommerce' );
						},
					],
					'metaData'    => [
						'type'        => [ 'list_of' => 'MetaDataInput' ],
						'description' => static function () {
							return __( 'Meta data.', 'graphql-for-ecommerce' );
						},
					],
					'sku'         => [
						'type'        => 'string',
						'description' => static function () {
							return __( 'Product SKU.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
