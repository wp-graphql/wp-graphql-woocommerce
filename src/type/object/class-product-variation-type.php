<?php
/**
 * WPObject Type - Product_Variation_Type
 *
 * Registers ProductVariation WPObject type
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Model\Product_Variation;

/**
 * Class Product_Variation_Type
 */
class Product_Variation_Type {
	/**
	 * Register ProductVariation type to the WPGraphQL schema
	 */
	public static function register() {
		wc_register_graphql_object_type(
			'ProductVariation',
			array(
				'description'       => __( 'A product variation object', 'wp-graphql-woocommerce' ),
				'interfaces'        => [ WPObjectType::node_interface() ],
				'fields'            => array(
					'id'                => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the product variation', 'wp-graphql-woocommerce' ),
					),
					'variationId'       => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
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
					),
					'regularPrice'      => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s regular price', 'wp-graphql-woocommerce' ),
					),
					'salePrice'         => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s sale price', 'wp-graphql-woocommerce' ),
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
							return DataSource::resolve_post_object( $source->imageId, $context );
						},
					),
				),
				'resolve_node'      => function( $node, $id, $type, $context ) {
					if ( 'product_variation' === $type ) {
						$node = Factory::resolve_crud_object( $id, $context );
					}

					return $node;
				},
				'resolve_node_type' => function( $type, $node ) {
					if ( is_a( $node, Product_Variation::class ) ) {
						$type = 'ProductVariation';
					}

					return $type;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'productVariation',
			array(
				'type'        => 'ProductVariation',
				'description' => __( 'A product variation object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'          => array(
						'type'        => 'ID',
						'description' => __( 'Get the product variation by its global ID', 'wp-graphql-woocommerce' ),
					),
					'variationId' => array(
						'type'        => 'Int',
						'description' => __( 'Get the product variation by its database ID', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$variation_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}

						$arg          = 'ID';
						$variation_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['variationId'] ) ) {
						$arg          = 'database ID';
						$variation_id = absint( $args['variationId'] );
					}

					$variation = Factory::resolve_crud_object( $variation_id, $context );
					if ( get_post( $variation_id )->post_type !== 'product_variation' ) {
						/* translators: no product variation found error message */
						throw new UserError( sprintf( __( 'No product variation exists with this %1$s: %2$s' ), $arg, $args['id'] ) );
					}

					return $variation;
				},
			)
		);
	}
}
