<?php
/**
 * Defines the "ProductWithPricing" interface.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Product_With_Pricing
 */
class Product_With_Pricing {
	/**
	 * Registers the "ProductWithPricing" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductWithPricing',
			[
				'description' => __( 'Products with pricing.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines fields of "ProductWithPricing".
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'           => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			],
			'databaseId'   => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			],
			'price'        => [
				'type'        => 'String',
				'description' => __( 'Product\'s active price', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => static function ( $source, $args ) {
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
				'resolve'     => static function ( $source, $args ) {
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
				'resolve'     => static function ( $source, $args ) {
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
		];
	}
}
