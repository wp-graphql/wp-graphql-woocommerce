<?php
/**
 * Register *IdTypeEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class - Id_Type_Enums
 */
class Id_Type_Enums {
	/**
	 * Register the Enum used for setting the field to identify WC crud objects by
	 *
	 * @access public
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'CouponIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Coupon. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
					'code'        => [
						'name'        => 'CODE',
						'value'       => 'code',
						'description' => static function () {
							return __( 'Coupon code.', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);

		register_graphql_enum_type(
			'OrderIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Order. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
					'order_key'   => [
						'name'        => 'ORDER_KEY',
						'value'       => 'order_key',
						'description' => static function () {
							return __( 'Order key.', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);

		register_graphql_enum_type(
			'ProductIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Product. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
					'slug'        => self::get_value( 'slug' ),
					'sku'         => [
						'name'        => 'SKU',
						'value'       => 'sku',
						'description' => static function () {
							return __( 'Unique store identifier for product.', 'wp-graphql-woocommerce' );
						},
					],
				],
			]
		);

		register_graphql_enum_type(
			'ProductVariationIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single ProductVariation. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'RefundIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Refund. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'ShippingMethodIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Shipping Method. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'ShippingZoneIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Shipping Zone. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'TaxRateIdTypeEnum',
			[
				'description' => static function () {
					return __( 'The Type of Identifier used to fetch a single Tax rate. Default is ID.', 'wp-graphql-woocommerce' );
				},
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);
	}

	/**
	 * Returns Enum Value definition.
	 *
	 * @param string $value  Enumeration value being retrieved.
	 * @return array
	 */
	private static function get_value( $value ) {
		switch ( $value ) {
			case 'slug':
				return [
					'name'        => 'SLUG',
					'value'       => 'slug',
					'description' => static function () {
						return __(
							'Identify a resource by the slug. Available to non-hierarchcial Types where the slug is a unique identifier.',
							'wp-graphql-woocommerce'
						);
					},
				];
			case 'database_id':
				return [
					'name'        => 'DATABASE_ID',
					'value'       => 'database_id',
					'description' => static function () {
						return __( 'Identify a resource by the Database ID.', 'wp-graphql-woocommerce' );
					},
				];
			case 'uri':
				return [
					'name'        => 'URI',
					'value'       => 'uri',
					'description' => static function () {
						return __( 'Identify a resource by the URI.', 'wp-graphql-woocommerce' );
					},
				];
			case 'id':
			default:
				return [
					'name'        => 'ID',
					'value'       => 'global_id',
					'description' => static function () {
						return __( 'Identify a resource by the (hashed) Global ID.', 'wp-graphql-woocommerce' );
					},
				];
		}//end switch
	}
}
