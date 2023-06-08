<?php
/**
 * WPObject Type - Product_Variation_Type
 *
 * Registers ProductVariation WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Product_Variation_Type
 */
class Product_Variation_Type {

	/**
	 * Register ProductVariation type to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductVariation',
			[
				'description' => __( 'A product variation object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [
					'Node',
					'NodeWithFeaturedImage',
					'ContentNode',
					'UniformResourceIdentifiable',
				],
				'fields'      => [
					'id'                => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The globally unique identifier for the product variation', 'wp-graphql-woocommerce' ),
					],
					'databaseId'        => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => __( 'The ID of the refund in the database', 'wp-graphql-woocommerce' ),
					],
					'name'              => [
						'type'        => 'String',
						'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
					],
					'date'              => [
						'type'        => 'String',
						'description' => __( 'Date variation created', 'wp-graphql-woocommerce' ),
					],
					'modified'          => [
						'type'        => 'String',
						'description' => __( 'Date variation last updated', 'wp-graphql-woocommerce' ),
					],
					'description'       => [
						'type'        => 'String',
						'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
					],
					'sku'               => [
						'type'        => 'String',
						'description' => __( 'Product variation SKU (Stock-keeping unit)', 'wp-graphql-woocommerce' ),
					],
					'price'             => [
						'type'        => 'String',
						'description' => __( 'Product variation\'s active price', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
                                // @codingStandardsIgnoreLine.
                                return $source->priceRaw;
							} else {
								return $source->price;
							}
						},
					],
					'regularPrice'      => [
						'type'        => 'String',
						'description' => __( 'Product variation\'s regular price', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
                                // @codingStandardsIgnoreLine.
                                return $source->regularPriceRaw;
							} else {
                                // @codingStandardsIgnoreLine.
                                return $source->regularPrice;
							}
						},
					],
					'salePrice'         => [
						'type'        => 'String',
						'description' => __( 'Product variation\'s sale price', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
                                // @codingStandardsIgnoreLine.
                                return $source->salePriceRaw;
							} else {
                                // @codingStandardsIgnoreLine.
                                return $source->salePrice;
							}
						},
					],
					'dateOnSaleFrom'    => [
						'type'        => 'String',
						'description' => __( 'Date on sale from', 'wp-graphql-woocommerce' ),
					],
					'dateOnSaleTo'      => [
						'type'        => 'String',
						'description' => __( 'Date on sale to', 'wp-graphql-woocommerce' ),
					],
					'onSale'            => [
						'type'        => 'Boolean',
						'description' => __( 'Is variation on sale?', 'wp-graphql-woocommerce' ),
					],
					'status'            => [
						'type'        => 'String',
						'description' => __( 'Variation status', 'wp-graphql-woocommerce' ),
					],
					'purchasable'       => [
						'type'        => 'Boolean',
						'description' => __( 'If product variation can be bought', 'wp-graphql-woocommerce' ),
					],
					'virtual'           => [
						'type'        => 'Boolean',
						'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
					],
					'downloadable'      => [
						'type'        => 'Boolean',
						'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
					],
					'downloads'         => [
						'type'        => [ 'list_of' => 'ProductDownload' ],
						'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
					],
					'downloadLimit'     => [
						'type'        => 'Int',
						'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
					],
					'downloadExpiry'    => [
						'type'        => 'Int',
						'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
					],
					'taxStatus'         => [
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
					],
					'taxClass'          => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'Product variation tax class', 'wp-graphql-woocommerce' ),
					],
					'manageStock'       => [
						'type'        => 'ManageStockEnum',
						'description' => __( 'if/how product variation stock is managed', 'wp-graphql-woocommerce' ),
					],
					'stockQuantity'     => [
						'type'        => 'Int',
						'description' => __( 'Product variation stock quantity', 'wp-graphql-woocommerce' ),
					],
					'stockStatus'       => [
						'type'        => 'StockStatusEnum',
						'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
					],
					'backorders'        => [
						'type'        => 'BackordersEnum',
						'description' => __( 'Product variation backorders', 'wp-graphql-woocommerce' ),
					],
					'backordersAllowed' => [
						'type'        => 'Boolean',
						'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
					],
					'weight'            => [
						'type'        => 'String',
						'description' => __( 'Product variation weight', 'wp-graphql-woocommerce' ),
					],
					'length'            => [
						'type'        => 'String',
						'description' => __( 'Product variation length', 'wp-graphql-woocommerce' ),
					],
					'width'             => [
						'type'        => 'String',
						'description' => __( 'Product variation width', 'wp-graphql-woocommerce' ),
					],
					'height'            => [
						'type'        => 'String',
						'description' => __( 'Product variation height', 'wp-graphql-woocommerce' ),
					],
					'menuOrder'         => [
						'type'        => 'Int',
						'description' => __( 'Menu order', 'wp-graphql-woocommerce' ),
					],
					'purchaseNote'      => [
						'type'        => 'String',
						'description' => __( 'Product variation purchase_note', 'wp-graphql-woocommerce' ),
					],
					'shippingClass'     => [
						'type'        => 'String',
						'description' => __( 'Product variation shipping class', 'wp-graphql-woocommerce' ),
					],
					'catalogVisibility' => [
						'type'        => 'CatalogVisibilityEnum',
						'description' => __( 'Product variation catalog visibility', 'wp-graphql-woocommerce' ),
					],
					'hasAttributes'     => [
						'type'        => 'Boolean',
						'description' => __( 'Does product variation have any visible attributes', 'wp-graphql-woocommerce' ),
					],
					'type'              => [
						'type'        => 'ProductTypesEnum',
						'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
					],
					'image'             => [
						'type'        => 'MediaItem',
						'description' => __( 'Product variation main image', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							return ! empty( $source->image_id )
								? $context->get_loader( 'post' )->load_deferred( $source->image_id )
								: null;
						},
					],

					'metaData'          => Meta_Data_Type::get_metadata_field_definition(),
				],
			]
		);
	}
}
