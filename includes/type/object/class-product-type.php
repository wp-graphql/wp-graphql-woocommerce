<?php
/**
 * WPObject Type - Product_Type
 *
 * Registers Product WPObject type and queries
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
use WPGraphQL\Data\DataSource;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Model\Product;

/**
 * Class Product_Type
 */
class Product_Type {
	/**
	 * Register Product type and queries to the WPGraphQL schema
	 */
	public static function register() {
		wc_register_graphql_object_type(
			'Product',
			array(
				'description'       => __( 'A product object', 'wp-graphql-woocommerce' ),
				'interfaces'        => [ WPObjectType::node_interface() ],
				'fields'            => array(
					'id'                => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the product', 'wp-graphql-woocommerce' ),
					),
					'productId'         => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
					),
					'slug'              => array(
						'type'        => 'String',
						'description' => __( 'Product slug', 'wp-graphql-woocommerce' ),
					),
					'date'              => array(
						'type'        => 'String',
						'description' => __( 'Date product created', 'wp-graphql-woocommerce' ),
					),
					'modified'          => array(
						'type'        => 'String',
						'description' => __( 'Date product last updated', 'wp-graphql-woocommerce' ),
					),
					'type'              => array(
						'type'        => 'ProductTypesEnum',
						'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
					),
					'name'              => array(
						'type'        => 'String',
						'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
					),
					'status'            => array(
						'type'        => 'String',
						'description' => __( 'Product status', 'wp-graphql-woocommerce' ),
					),
					'featured'          => array(
						'type'        => 'Boolean',
						'description' => __( 'If the product is featured', 'wp-graphql-woocommerce' ),
					),
					'catalogVisibility' => array(
						'type'        => 'CatalogVisibilityEnum',
						'description' => __( 'Catalog visibility', 'wp-graphql-woocommerce' ),
					),
					'description'       => array(
						'type'        => 'String',
						'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
					),
					'shortDescription'  => array(
						'type'        => 'String',
						'description' => __( 'Product short description', 'wp-graphql-woocommerce' ),
					),
					'sku'               => array(
						'type'        => 'String',
						'description' => __( 'Product SKU', 'wp-graphql-woocommerce' ),
					),
					'price'             => array(
						'type'        => 'String',
						'args'        => array(
							'max' => array(
								'type'        => 'Boolean',
								'description' => __( 'Show maximum price (Variable type product)', 'wp-graphql-woocommerce' ),
							),
						),
						'description' => __( 'Product\'s active price', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $product, array $args ) {
							if ( ! empty( $args['max'] ) && $args['max'] ) {
								// @codingStandardsIgnoreLine
								return $product->priceMax;
							}
							// @codingStandardsIgnoreLine
							return $product->price;
						},
					),
					'regularPrice'      => array(
						'type'        => 'String',
						'args'        => array(
							'max' => array(
								'type'        => 'Boolean',
								'description' => __( 'Show maximum price (Variable type product)', 'wp-graphql-woocommerce' ),
							),
						),
						'description' => __( 'Product\'s regular price', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $product, array $args ) {
							if ( ! empty( $args['max'] ) && $args['max'] ) {
								// @codingStandardsIgnoreLine
								return $product->regularPriceMax;
							}
							// @codingStandardsIgnoreLine
							return $product->regularPrice;
						},
					),
					'salePrice'         => array(
						'type'        => 'String',
						'args'        => array(
							'max' => array(
								'type'        => 'Boolean',
								'description' => __( 'Show maximum price (Variable type product)', 'wp-graphql-woocommerce' ),
							),
						),
						'description' => __( 'Product\'s sale price', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $product, array $args ) {
							if ( ! empty( $args['max'] ) && $args['max'] ) {
								// @codingStandardsIgnoreLine
								return $product->salePriceMax;
							}
							// @codingStandardsIgnoreLine
							return $product->salePrice;
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
					'totalSales'        => array(
						'type'        => 'Int',
						'description' => __( 'Number total of sales', 'wp-graphql-woocommerce' ),
					),
					'taxStatus'         => array(
						'type'        => 'TaxStatusEnum',
						'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
					),
					'taxClass'          => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Tax class', 'wp-graphql-woocommerce' ),
					),
					'manageStock'       => array(
						'type'        => 'Boolean',
						'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
					),
					'stockQuantity'     => array(
						'type'        => 'Int',
						'description' => __( 'Number of items available for sale', 'wp-graphql-woocommerce' ),
					),
					'stockStatus'       => array(
						'type'        => 'StockStatusEnum',
						'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
					),
					'backorders'        => array(
						'type'        => 'BackordersEnum',
						'description' => __( 'Product backorders status', 'wp-graphql-woocommerce' ),
					),
					'soldIndividually'  => array(
						'type'        => 'Boolean',
						'description' => __( 'If should be sold individually', 'wp-graphql-woocommerce' ),
					),
					'weight'            => array(
						'type'        => 'String',
						'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
					),
					'length'            => array(
						'type'        => 'String',
						'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
					),
					'width'             => array(
						'type'        => 'String',
						'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
					),
					'height'            => array(
						'type'        => 'String',
						'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
					),
					'reviewsAllowed'    => array(
						'type'        => 'Boolean',
						'description' => __( 'If reviews are allowed', 'wp-graphql-woocommerce' ),
					),
					'purchaseNote'      => array(
						'type'        => 'String',
						'description' => __( 'Purchase note', 'wp-graphql-woocommerce' ),
					),
					'menuOrder'         => array(
						'type'        => 'Int',
						'description' => __( 'Menu order', 'wp-graphql-woocommerce' ),
					),
					'virtual'           => array(
						'type'        => 'Boolean',
						'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
					),
					'downloadExpiry'    => array(
						'type'        => 'Int',
						'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
					),
					'downloadable'      => array(
						'type'        => 'Boolean',
						'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
					),
					'downloadLimit'     => array(
						'type'        => 'Int',
						'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
					),
					'ratingCount'       => array(
						'type'        => 'RatingCounter',
						'description' => __( 'Product rating counts', 'wp-graphql-woocommerce' ),
					),
					'averageRating'     => array(
						'type'        => 'Float',
						'description' => __( 'Product average count', 'wp-graphql-woocommerce' ),
					),
					'reviewCount'       => array(
						'type'        => 'Int',
						'description' => __( 'Product review count', 'wp-graphql-woocommerce' ),
					),
					'parent'            => array(
						'type'        => 'Product',
						'description' => __( 'Parent product', 'wp-graphql-woocommerce' ),
					),
					'image'             => array(
						'type'        => 'MediaItem',
						'description' => __( 'Main image', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							// @codingStandardsIgnoreLine
							return DataSource::resolve_post_object( $source->imageId, $context );
						},
					),
					'shippingClassId'   => array(
						'type'        => 'Int',
						'description' => __( 'shipping class ID', 'wp-graphql-woocommerce' ),
					),
					'downloads'         => array(
						'type'        => array( 'list_of' => 'ProductDownload' ),
						'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
					),
					'onSale'            => array(
						'type'        => 'Boolean',
						'description' => __( 'Is product on sale?', 'wp-graphql-woocommerce' ),
					),
					'purchasable'       => array(
						'type'        => 'Boolean',
						'description' => __( 'Can product be purchased?', 'wp-graphql-woocommerce' ),
					),
					'externalUrl'       => array(
						'type'        => 'String',
						'description' => __( 'External product url', 'wp-graphql-woocommerce' ),
					),
					'buttonText'        => array(
						'type'        => 'String',
						'description' => __( 'External product Buy button text', 'wp-graphql-woocommerce' ),
					),
					'backordersAllowed' => array(
						'type'        => 'Boolean',
						'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
					),
					'shippingRequired'  => array(
						'type'        => 'Boolean',
						'description' => __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' ),
					),
					'shippingTaxable'   => array(
						'type'        => 'Boolean',
						'description' => __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve_node'      => function( $node, $id, $type, $context ) {
					if ( 'product' === $type ) {
						$node = Factory::resolve_crud_object( $id, $context );
					}

					return $node;
				},
				'resolve_node_type' => function( $type, $node ) {
					if ( is_a( $node, Product::class ) ) {
						$type = 'Product';
					}

					return $type;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'product',
			array(
				'type'        => 'Product',
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id' => array(
						'type' => array( 'non_null' => 'ID' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );
					if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}
					$product_id = absint( $id_components['id'] );
					return Factory::resolve_crud_object( $product_id, $context );
				},
			)
		);

		$post_by_args = array(
			'id'        => array(
				'type'        => 'ID',
				'description' => __( 'Get the product by its global ID', 'wp-graphql-woocommerce' ),
			),
			'productId' => array(
				'type'        => 'Int',
				'description' => __( 'Get the product by its database ID', 'wp-graphql-woocommerce' ),
			),
		);

		register_graphql_field(
			'RootQuery',
			'productBy',
			array(
				'type'        => 'Product',
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'args'        => $post_by_args,
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$product_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}
						$product_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['productId'] ) ) {
						$product_id = absint( $args['productId'] );
					}

					$product = Factory::resolve_crud_object( $product_id, $context );
					if ( get_post( $product_id )->post_type !== 'product' ) {
						/* translators: not coupon found error message */
						throw new UserError( sprintf( __( 'No product exists with this id: %1$s' ), $args['id'] ) );
					}

					return $product;
				},
			)
		);
	}
}
