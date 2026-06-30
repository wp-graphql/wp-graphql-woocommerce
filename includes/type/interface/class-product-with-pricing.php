<?php
/**
 * Defines the "ProductWithPricing" interface.
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

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
				'description' => static function () {
					return __( 'Products with pricing.', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => 'wc_graphql_resolve_product_type',
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
				'description' => static function () {
					return __( 'Product or variation global ID', 'graphql-for-ecommerce' );
				},
			],
			'databaseId'   => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'Product or variation ID', 'graphql-for-ecommerce' );
				},
			],
			'price'        => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s active price', 'graphql-for-ecommerce' );
				},
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => static function () {
							return __( 'Format of the price', 'graphql-for-ecommerce' );
						},
					],
				],
				'resolve'     => static function ( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
                        // @codingStandardsIgnoreLine.
                        return $source->priceRaw;
					} else {
						// @codingStandardsIgnoreLine.
						return $source->price;
					}
				},
			],
			'regularPrice' => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product\'s regular price', 'graphql-for-ecommerce' );
				},
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => static function () {
							return __( 'Format of the price', 'graphql-for-ecommerce' );
						},
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
				'description' => static function () {
					return __( 'Product\'s sale price', 'graphql-for-ecommerce' );
				},
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => static function () {
							return __( 'Format of the price', 'graphql-for-ecommerce' );
						},
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
				'description' => static function () {
					return __( 'Tax status', 'graphql-for-ecommerce' );
				},
			],
			'taxClass'     => [
				'type'        => 'TaxClassEnum',
				'description' => static function () {
					return __( 'Tax class', 'graphql-for-ecommerce' );
				},
			],
		];
	}
}
