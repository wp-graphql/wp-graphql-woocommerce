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

use WPGraphQL\WooCommerce\Model\Product as Model;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;


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
	 * @param array $other_interfaces Other interfaces to merge with the product interfaces.
	 * 
	 * @return array
	 */
	public static function get_product_interfaces( $other_interfaces = [] ) {
		return array_merge(
			[
				'Node',
				'Product',
				'ProductUnion',
				'ProductWithAttributes',
				'NodeWithComments',
				'NodeWithContentEditor',
				'NodeWithFeaturedImage',
				'ContentNode',
				'UniformResourceIdentifiable',
			],
			$other_interfaces
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
				'eagerlyLoadType' => true,
				'description'     => __( 'A simple product object', 'wp-graphql-woocommerce' ),
				'interfaces'      => self::get_product_interfaces(
					[
						'DownloadableProduct',
						'InventoriedProduct',
						'ProductWithDimensions',
						'ProductWithPricing',
					]
				),
				'fields'          => [],
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
				'eagerlyLoadType' => true,
				'description'     => __( 'A variable product object', 'wp-graphql-woocommerce' ),
				'interfaces'      => self::get_product_interfaces(
					[
						'InventoriedProduct',
						'ProductWithDimensions',
						'ProductWithPricing',
						'ProductWithVariations',
					]
				),
				'fields'          => [],
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
				'eagerlyLoadType' => true,
				'description'     => __( 'A external product object', 'wp-graphql-woocommerce' ),
				'interfaces'      => self::get_product_interfaces( [ 'ProductWithPricing' ] ),
				'fields'          => array_merge(
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
				'eagerlyLoadType' => true,
				'description'     => __( 'A group product object', 'wp-graphql-woocommerce' ),
				'interfaces'      => self::get_product_interfaces( [ 'ProductWithPricing' ] ),
				'fields'          => [
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
						'resolve'     => static function ( Model $source ) {
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
				],
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
				'eagerlyLoadType' => true,
				'description'     => __( 'A product object for a product type that is unsupported by the current API.', 'wp-graphql-woocommerce' ),
				'interfaces'      => self::get_product_interfaces(
					[
						'DownloadableProduct',
						'InventoriedProduct',
						'ProductWithDimensions',
						'ProductWithPricing',
					]
				),
				'fields'          => [
					'type' => [
						'type'        => 'ProductTypesEnum',
						'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
						'resolve'     => static function () {
							return 'unsupported';
						},
					],
				],
			]
		);
	}
}
