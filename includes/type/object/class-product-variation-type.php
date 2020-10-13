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
use WPGraphQL\Data\DataSource;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Product_Variation_Type
 */
class Product_Variation_Type {

	/**
	 * Register ProductVariation type to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductVariation',
			array(
				'description' => __( 'A product variation object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array(
					'Node',
					'NodeWithFeaturedImage',
					'ContentNode',
					'UniformResourceIdentifiable',
				),
				'fields'      => array(
					'id'                => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the product variation', 'wp-graphql-woocommerce' ),
					),
					'databaseId'        => array(
						'type'        => array( 'non_null' => 'Int' ),
						'description' => __( 'The ID of the refund in the database', 'wp-graphql-woocommerce' ),
					),
					'name'              => array(
						'type'        => 'String',
						'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
					),
					'date'              => array(
						'type'        => 'String',
						'description' => __( 'Date variation created', 'wp-graphql-woocommerce' ),
					),
					'modified'          => array(
						'type'        => 'String',
						'description' => __( 'Date variation last updated', 'wp-graphql-woocommerce' ),
					),
					'description'       => array(
						'type'        => 'String',
						'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
					),
					'sku'               => array(
						'type'        => 'String',
						'description' => __( 'Product variation SKU (Stock-keeping unit)', 'wp-graphql-woocommerce' ),
					),
					'price'             => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s active price', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->priceRaw;
							} else {
								return $source->price;
							}
						},
					),
					'regularPrice'      => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s regular price', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->regularPriceRaw;
							} else {
								// @codingStandardsIgnoreLine.
								return $source->regularPrice;
							}
						},
					),
					'salePrice'         => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s sale price', 'wp-graphql-woocommerce' ),
						'args'        => array(
							'format' => array(
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							),
						),
						'resolve'     => function( $source, $args ) {
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								// @codingStandardsIgnoreLine.
								return $source->salePriceRaw;
							} else {
								// @codingStandardsIgnoreLine.
								return $source->salePrice;
							}
						},
					),
					'dateOnSaleFrom'    => array(
						'type'        => 'String',
						'description' => __( 'Date on sale from', 'wp-graphql-woocommerce' ),
					),
					'dateOnSaleTo'      => array(
						'type'        => 'String',
						'description' => __( 'Date on sale to', 'wp-graphql-woocommerce' ),
					),
					'onSale'            => array(
						'type'        => 'Boolean',
						'description' => __( 'Is variation on sale?', 'wp-graphql-woocommerce' ),
					),
					'status'            => array(
						'type'        => 'String',
						'description' => __( 'Variation status', 'wp-graphql-woocommerce' ),
					),
					'purchasable'       => array(
						'type'        => 'Boolean',
						'description' => __( 'If product variation can be bought', 'wp-graphql-woocommerce' ),
					),
					'virtual'           => array(
						'type'        => 'Boolean',
						'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
					),
					'downloadable'      => array(
						'type'        => 'Boolean',
						'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
					),
					'downloads'         => array(
						'type'        => array( 'list_of' => 'ProductDownload' ),
						'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
					),
					'downloadLimit'     => array(
						'type'        => 'Int',
						'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
					),
					'downloadExpiry'    => array(
						'type'        => 'Int',
						'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
					),
					'taxStatus'         => array(
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
					),
					'taxClass'          => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Product variation tax class', 'wp-graphql-woocommerce' ),
					),
					'manageStock'       => array(
						'type'        => 'ManageStockEnum',
						'description' => __( 'if/how product variation stock is managed', 'wp-graphql-woocommerce' ),
					),
					'stockQuantity'     => array(
						'type'        => 'Int',
						'description' => __( 'Product variation stock quantity', 'wp-graphql-woocommerce' ),
					),
					'stockStatus'       => array(
						'type'        => 'StockStatusEnum',
						'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
					),
					'backorders'        => array(
						'type'        => 'BackordersEnum',
						'description' => __( 'Product variation backorders', 'wp-graphql-woocommerce' ),
					),
					'backordersAllowed' => array(
						'type'        => 'Boolean',
						'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
					),
					'weight'            => array(
						'type'        => 'String',
						'description' => __( 'Product variation weight', 'wp-graphql-woocommerce' ),
					),
					'length'            => array(
						'type'        => 'String',
						'description' => __( 'Product variation length', 'wp-graphql-woocommerce' ),
					),
					'width'             => array(
						'type'        => 'String',
						'description' => __( 'Product variation width', 'wp-graphql-woocommerce' ),
					),
					'height'            => array(
						'type'        => 'String',
						'description' => __( 'Product variation height', 'wp-graphql-woocommerce' ),
					),
					'menuOrder'         => array(
						'type'        => 'Int',
						'description' => __( 'Menu order', 'wp-graphql-woocommerce' ),
					),
					'purchaseNote'      => array(
						'type'        => 'String',
						'description' => __( 'Product variation purchase_note', 'wp-graphql-woocommerce' ),
					),
					'shippingClass'     => array(
						'type'        => 'String',
						'description' => __( 'Product variation shipping class', 'wp-graphql-woocommerce' ),
					),
					'catalogVisibility' => array(
						'type'        => 'CatalogVisibilityEnum',
						'description' => __( 'Product variation catalog visibility', 'wp-graphql-woocommerce' ),
					),
					'hasAttributes'     => array(
						'type'        => 'Boolean',
						'description' => __( 'Does product variation have any visible attributes', 'wp-graphql-woocommerce' ),
					),
					'type'              => array(
						'type'        => 'ProductTypesEnum',
						'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
					),
					'image'             => array(
						'type'        => 'MediaItem',
						'description' => __( 'Product variation main image', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							// @codingStandardsIgnoreLine
							return DataSource::resolve_post_object( $source->image_id, $context );
						},
					),
				),
			)
		);
	}
}
