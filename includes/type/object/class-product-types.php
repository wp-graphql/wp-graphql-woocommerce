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
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Model\Product as Model;
use WPGraphQL\WooCommerce\Type\WPInterface\Product;


/**
 * Class Product_Types
 */
class Product_Types {

	/**
	 * Registers product types to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		self::register_simple_product_type();
		self::register_variable_product_type();
		self::register_external_product_type();
		self::register_group_product_type();

		if ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
			self::register_unsupported_product_type();
		}
	}

	/**
	 * Returns the GraphQL interfaces for product types.
	 *
	 * @return array
	 */
	public static function get_product_interfaces() {
		return [
			'Node',
			'Product',
			'NodeWithComments',
			'NodeWithContentEditor',
			'NodeWithFeaturedImage',
			'ContentNode',
			'UniformResourceIdentifiable',
		];
	}

	/**
	 * Defines fields related to product inventory.
	 *
	 * @param array $fields  Fields array for overwriting any of the inventory fields.
	 *
	 * @return array
	 */
	public static function get_inventory_fields( $fields = [] ) {
		return array_merge(
			[
				'manageStock'       => [
					'type'        => 'Boolean',
					'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
				],
				'stockQuantity'     => [
					'type'        => 'Int',
					'description' => __( 'Number of items available for sale', 'wp-graphql-woocommerce' ),
				],
				'backorders'        => [
					'type'        => 'BackordersEnum',
					'description' => __( 'Product backorders status', 'wp-graphql-woocommerce' ),
				],
				'soldIndividually'  => [
					'type'        => 'Boolean',
					'description' => __( 'If should be sold individually', 'wp-graphql-woocommerce' ),
				],
				'backordersAllowed' => [
					'type'        => 'Boolean',
					'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
				],
				'stockStatus'       => [
					'type'        => 'StockStatusEnum',
					'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
				],
			],
			$fields
		);
	}

	/**
	 * Defines fields related to product shipping.
	 *
	 * @param array $fields  Fields array for overwriting any of shipping fields.
	 *
	 * @return array
	 */
	public static function get_shipping_fields( $fields = [] ) {
		return array_merge(
			[
				'weight'           => [
					'type'        => 'String',
					'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
				],
				'length'           => [
					'type'        => 'String',
					'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
				],
				'width'            => [
					'type'        => 'String',
					'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
				],
				'height'           => [
					'type'        => 'String',
					'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
				],
				'shippingClassId'  => [
					'type'        => 'Int',
					'description' => __( 'shipping class ID', 'wp-graphql-woocommerce' ),
				],
				'shippingRequired' => [
					'type'        => 'Boolean',
					'description' => __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' ),
				],
				'shippingTaxable'  => [
					'type'        => 'Boolean',
					'description' => __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' ),
				],
			],
			$fields
		);
	}

	/**
	 * Defines fields related to pricing and taxes.
	 *
	 * @param array $fields  Fields array for overwriting any of the pricing and tax fields.
	 *
	 * @return array
	 */
	public static function get_pricing_and_tax_fields( $fields = [] ) {
		return array_merge(
			[
				'price'        => [
					'type'        => 'String',
					'description' => __( 'Product\'s active price', 'wp-graphql-woocommerce' ),
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
				'regularPrice' => [
					'type'        => 'String',
					'description' => __( 'Product\'s regular price', 'wp-graphql-woocommerce' ),
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
				'salePrice'    => [
					'type'        => 'String',
					'description' => __( 'Product\'s sale price', 'wp-graphql-woocommerce' ),
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
				'taxStatus'    => [
					'type'        => 'TaxStatusEnum',
					'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
				],
				'taxClass'     => [
					'type'        => 'TaxClassEnum',
					'description' => __( 'Tax class', 'wp-graphql-woocommerce' ),
				],
			],
			$fields
		);
	}

	/**
	 * Defines fields related to virtual product info.
	 *
	 * @param array $fields  Fields array for overwriting any of the virtual data fields.
	 *
	 * @return array
	 */
	public static function get_virtual_data_fields( $fields = [] ) {
		return array_merge(
			[
				'virtual'        => [
					'type'        => 'Boolean',
					'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
				],
				'downloadExpiry' => [
					'type'        => 'Int',
					'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
				],
				'downloadable'   => [
					'type'        => 'Boolean',
					'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
				],
				'downloadLimit'  => [
					'type'        => 'Int',
					'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
				],
				'downloads'      => [
					'type'        => [ 'list_of' => 'ProductDownload' ],
					'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
				],
			],
			$fields
		);
	}

	/**
	 * Register "SimpleProduct" type.
	 *
	 * @return void
	 */
	private static function register_simple_product_type() {
		register_graphql_object_type(
			'SimpleProduct',
			[
				'description' => __( 'A simple product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => self::get_product_interfaces(),
				'fields'      => array_merge(
					Product::get_fields(),
					self::get_pricing_and_tax_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields(),
					self::get_virtual_data_fields()
				),
			]
		);
	}

	/**
	 * Registers "VariableProduct" type.
	 *
	 * @return void
	 */
	private static function register_variable_product_type() {
		register_graphql_object_type(
			'VariableProduct',
			[
				'description' => __( 'A variable product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => self::get_product_interfaces(),
				'fields'      => array_merge(
					Product::get_fields(),
					self::get_pricing_and_tax_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields()
				),
			]
		);
	}

	/**
	 * Registers "ExternalProduct" type.
	 *
	 * @return void
	 */
	private static function register_external_product_type() {
		register_graphql_object_type(
			'ExternalProduct',
			[
				'description' => __( 'A external product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => self::get_product_interfaces(),
				'fields'      => array_merge(
					Product::get_fields(),
					self::get_pricing_and_tax_fields(),
					[
						'externalUrl' => [
							'type'        => 'String',
							'description' => __( 'External product url', 'wp-graphql-woocommerce' ),
						],
						'buttonText'  => [
							'type'        => 'String',
							'description' => __( 'External product Buy button text', 'wp-graphql-woocommerce' ),
						],
					]
				),
			]
		);
	}

	/**
	 * Registers "GroupProduct" type.
	 *
	 * @return void
	 */
	private static function register_group_product_type() {
		register_graphql_object_type(
			'GroupProduct',
			[
				'description' => __( 'A group product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => self::get_product_interfaces(),
				'fields'      => array_merge(
					Product::get_fields(),
					[
						'addToCartText'        => [
							'type'        => 'String',
							'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
						],
						'addToCartDescription' => [
							'type'        => 'String',
							'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
						],
						'price'                => [
							'type'        => 'String',
							'description' => __( 'Products\' price range', 'wp-graphql-woocommerce' ),
							'resolve'     => function( Model $source ) {
								$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
								$child_prices     = [];
								$children         = array_filter( array_map( 'wc_get_product', $source->grouped_ids ) );
								$children         = array_filter( $children, 'wc_products_array_filter_visible_grouped' );

								foreach ( $children as $child ) {
									if ( ! $child ) {
										continue;
									}

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
									return wc_graphql_price_range( $min_price, $max_price );
								}

								return wc_graphql_price( $min_price );
							},
						],
					]
				),
			]
		);
	}

	/**
	 * Register "SimpleProduct" type.
	 *
	 * @return void
	 */
	private static function register_unsupported_product_type() {
		register_graphql_object_type(
			WooGraphQL::get_supported_product_type(),
			[
				'description' => __( 'A product object for a product type that is unsupported by the current API.', 'wp-graphql-woocommerce' ),
				'interfaces'  => self::get_product_interfaces(),
				'fields'      => array_merge(
					Product::get_fields(),
					[
						'type' => [
							'type'        => 'ProductTypesEnum',
							'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
							'resolve'     => function () {
								return 'unsupported';
							},
						],
					],
					self::get_pricing_and_tax_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields(),
					self::get_virtual_data_fields()
				),
			]
		);
	}
}
