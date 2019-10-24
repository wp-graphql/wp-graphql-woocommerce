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
	 * Registers product types to the WPGraphQL schema
	 */
	public static function register() {
		self::register_simple_product_type();
		self::register_variable_product_type();
		self::register_external_product_type();
		self::register_group_product_type();
	}

	/**
	 * Defines fields shared by all Product types.
	 */
	private static function get_shared_fields() {
		return array(
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
				'args'        => array(
					'format' => array(
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->descriptionRaw;
					}
					return $source->description;
				},
			),
			'shortDescription'  => array(
				'type'        => 'String',
				'description' => __( 'Product short description', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'format' => array(
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->shortDescriptionRaw;
					}
					// @codingStandardsIgnoreLine.
					return $source->shortDescription;
				},
			),
			'sku'               => array(
				'type'        => 'String',
				'description' => __( 'Product SKU', 'wp-graphql-woocommerce' ),
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
			'averageRating'     => array(
				'type'        => 'Float',
				'description' => __( 'Product average count', 'wp-graphql-woocommerce' ),
			),
			'reviewCount'       => array(
				'type'        => 'Int',
				'description' => __( 'Product review count', 'wp-graphql-woocommerce' ),
			),
			'parent'            => array(
				'type'        => 'ProductUnion',
				'description' => __( 'Parent product', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context ) {
					return Factory::resolve_crud_object( $source->parent_id, $context );
				},
			),
			'image'             => array(
				'type'        => 'MediaItem',
				'description' => __( 'Main image', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context ) {
					// @codingStandardsIgnoreLine.
					if ( empty( $source->image_id ) || ! absint( $source->image_id ) ) {
						return null;
					}
					return DataSource::resolve_post_object( $source->image_id, $context );
				},
			),
			'onSale'            => array(
				'type'        => 'Boolean',
				'description' => __( 'Is product on sale?', 'wp-graphql-woocommerce' ),
			),
			'purchasable'       => array(
				'type'        => 'Boolean',
				'description' => __( 'Can product be purchased?', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines fields related to product inventory.
	 */
	private static function get_inventory_fields() {
		return array(
			'manageStock'       => array(
				'type'        => 'Boolean',
				'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
			),
			'stockQuantity'     => array(
				'type'        => 'Int',
				'description' => __( 'Number of items available for sale', 'wp-graphql-woocommerce' ),
			),
			'backorders'        => array(
				'type'        => 'BackordersEnum',
				'description' => __( 'Product backorders status', 'wp-graphql-woocommerce' ),
			),
			'soldIndividually'  => array(
				'type'        => 'Boolean',
				'description' => __( 'If should be sold individually', 'wp-graphql-woocommerce' ),
			),
			'backordersAllowed' => array(
				'type'        => 'Boolean',
				'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines fields related to product shipping.
	 */
	private static function get_shipping_fields() {
		return array(
			'weight'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
			),
			'length'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
			),
			'width'            => array(
				'type'        => 'String',
				'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
			),
			'height'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
			),
			'shippingClassId'  => array(
				'type'        => 'Int',
				'description' => __( 'shipping class ID', 'wp-graphql-woocommerce' ),
			),
			'shippingRequired' => array(
				'type'        => 'Boolean',
				'description' => __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' ),
			),
			'shippingTaxable'  => array(
				'type'        => 'Boolean',
				'description' => __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines fields not found in grouped-type products.
	 */
	private static function get_non_grouped_fields() {
		return array(
			'price'        => array(
				'type'        => 'String',
				'description' => __( 'Product\'s active price', 'wp-graphql-woocommerce' ),
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
			'regularPrice' => array(
				'type'        => 'String',
				'description' => __( 'Product\'s regular price', 'wp-graphql-woocommerce' ),
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
			'salePrice'    => array(
				'type'        => 'String',
				'description' => __( 'Product\'s sale price', 'wp-graphql-woocommerce' ),
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
			'taxStatus'    => array(
				'type'        => 'TaxStatusEnum',
				'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
			),
			'taxClass'     => array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'Tax class', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Register "SimpleProduct" type
	 */
	private static function register_simple_product_type() {
		wc_register_graphql_object_type(
			'SimpleProduct',
			array(
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array_merge(
					self::get_shared_fields(),
					self::get_non_grouped_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields(),
					array(
						'virtual'        => array(
							'type'        => 'Boolean',
							'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
						),
						'downloadExpiry' => array(
							'type'        => 'Int',
							'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
						),
						'downloadable'   => array(
							'type'        => 'Boolean',
							'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
						),
						'downloadLimit'  => array(
							'type'        => 'Int',
							'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
						),
						'downloads'      => array(
							'type'        => array( 'list_of' => 'ProductDownload' ),
							'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
						),
						'stockStatus'    => array(
							'type'        => 'StockStatusEnum',
							'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
						),
					)
				),
			)
		);
	}

	/**
	 * Registers "VariableProduct" type.
	 */
	private static function register_variable_product_type() {
		wc_register_graphql_object_type(
			'VariableProduct',
			array(
				'description' => __( 'A variable product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array_merge(
					self::get_shared_fields(),
					self::get_non_grouped_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields()
				),
			)
		);
	}

	/**
	 * Registers "ExternalProduct" type.
	 */
	private static function register_external_product_type() {
		wc_register_graphql_object_type(
			'ExternalProduct',
			array(
				'description' => __( 'A external product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array_merge(
					self::get_shared_fields(),
					self::get_non_grouped_fields(),
					array(
						'externalUrl' => array(
							'type'        => 'String',
							'description' => __( 'External product url', 'wp-graphql-woocommerce' ),
						),
						'buttonText'  => array(
							'type'        => 'String',
							'description' => __( 'External product Buy button text', 'wp-graphql-woocommerce' ),
						),
					)
				),
			)
		);
	}

	/**
	 * Registers "GroupProduct" type.
	 */
	public static function register_group_product_type() {
		wc_register_graphql_object_type(
			'GroupProduct',
			array(
				'description' => __( 'A group product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array_merge(
					self::get_shared_fields(),
					array(
						'addToCartText'        => array(
							'type'        => 'String',
							'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
						),
						'addToCartDescription' => array(
							'type'        => 'String',
							'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
						),
					)
				),
			)
		);
	}
}
