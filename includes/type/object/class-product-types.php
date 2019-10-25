<?php
/**
 * WPObjectType - *Product
 *
 * Registers product types and queries
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
use WPGraphQL\Extensions\WooCommerce\Type\WPInterface\Product;

/**
 * Class Product_Types
 */
class Product_Types {
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
	 * Register "SimpleProduct" type.
	 */
	private static function register_simple_product_type() {
		register_graphql_object_type(
			'SimpleProduct',
			array(
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
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
		register_graphql_object_type(
			'VariableProduct',
			array(
				'description' => __( 'A variable product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
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
		register_graphql_object_type(
			'ExternalProduct',
			array(
				'description' => __( 'A external product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
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
		register_graphql_object_type(
			'GroupProduct',
			array(
				'description' => __( 'A group product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
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
