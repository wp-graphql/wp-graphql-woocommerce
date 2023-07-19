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
				'description' => __( 'The Type of Identifier used to fetch a single Coupon. Default is ID.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
					'code'        => [
						'name'        => 'CODE',
						'value'       => 'code',
						'description' => __( 'Coupon code.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_enum_type(
			'OrderIdTypeEnum',
			[
				'description' => __( 'The Type of Identifier used to fetch a single Order. Default is ID.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'id'           => self::get_value( 'id' ),
					'database_id'  => self::get_value( 'database_id' ),
					'order_number' => [
						'name'        => 'ORDER_NUMBER',
						'value'       => 'order_number',
						'description' => __( 'Order number.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_enum_type(
			'ProductIdTypeEnum',
			[
				'description' => __( 'The Type of Identifier used to fetch a single Product. Default is ID.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
					'slug'        => self::get_value( 'slug' ),
					'sku'         => [
						'name'        => 'SKU',
						'value'       => 'sku',
						'description' => __( 'Unique store identifier for product.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_enum_type(
			'ProductVariationIdTypeEnum',
			[
				'description' => __( 'The Type of Identifier used to fetch a single ProductVariation. Default is ID.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'RefundIdTypeEnum',
			[
				'description' => __( 'The Type of Identifier used to fetch a single Refund. Default is ID.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'ShippingMethodIdTypeEnum',
			[
				'description' => __( 'The Type of Identifier used to fetch a single Shipping Method. Default is ID.', 'wp-graphql-woocommerce' ),
				'values'      => [
					'id'          => self::get_value( 'id' ),
					'database_id' => self::get_value( 'database_id' ),
				],
			]
		);

		register_graphql_enum_type(
			'TaxRateIdTypeEnum',
			[
				'description' => __( 'The Type of Identifier used to fetch a single Tax rate. Default is ID.', 'wp-graphql-woocommerce' ),
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
					'description' => __(
						'Identify a resource by the slug. Available to non-hierarchcial Types where the slug is a unique identifier.',
						'wp-graphql-woocommerce'
					),
				];
			case 'database_id':
				return [
					'name'        => 'DATABASE_ID',
					'value'       => 'database_id',
					'description' => __( 'Identify a resource by the Database ID.', 'wp-graphql-woocommerce' ),
				];
			case 'uri':
				return [
					'name'        => 'URI',
					'value'       => 'uri',
					'description' => __( 'Identify a resource by the URI.', 'wp-graphql-woocommerce' ),
				];
			case 'id':
			default:
				return [
					'name'        => 'ID',
					'value'       => 'global_id',
					'description' => __( 'Identify a resource by the (hashed) Global ID.', 'wp-graphql-woocommerce' ),
				];
		}//end switch
	}
}
