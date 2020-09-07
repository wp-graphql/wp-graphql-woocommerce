<?php
/**
 * WPObjectType - *Product
 *
 * Registers product types and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.3.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Type\WPInterface\Product;

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
						'price'                => array(
							'type'        => 'String',
							'description' => __( 'Products\' price range', 'wp-graphql-woocommerce' ),
							'resolve'     => function( $source ) {
								$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
								$child_prices     = array();
								$children         = array_filter( array_map( 'wc_get_product', $source->grouped_ids ), 'wc_products_array_filter_visible_grouped' );

								foreach ( $children as $child ) {
									if ( '' !== $child->get_price() ) {
										$child_prices[] = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $child ) : wc_get_price_excluding_tax( $child );
									}
								}

								if ( ! empty( $child_prices ) ) {
									$min_price = min( $child_prices );
									$max_price = max( $child_prices );
								} else {
									$min_price = '';
									$max_price = '';
								}

								if ( empty( $min_price ) ) {
									return null;
								}

								if ( $min_price !== $max_price ) {
									return \wc_graphql_price_range( $min_price, $max_price );
								}

								return \wc_graphql_price( $min_price );
							},
						),
					)
				),
			)
		);
	}
}
