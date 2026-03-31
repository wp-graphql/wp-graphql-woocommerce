<?php
/**
 * WPObject Type - Coupon_Type
 *
 * Registers Coupon WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Coupon_Type
 */
class Coupon_Type {
	/**
	 * Register Coupon type and queries to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'Coupon',
			[
				'description' => static function () {
					return __( 'A coupon object', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'                 => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
					return __( 'The globally unique identifier for the coupon', 'wp-graphql-woocommerce' );
				},
					],
					'databaseId'         => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'The ID of the coupon in the database', 'wp-graphql-woocommerce' );
				},
					],
					'code'               => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Coupon code', 'wp-graphql-woocommerce' );
				},
					],
					'date'               => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Date coupon created', 'wp-graphql-woocommerce' );
				},
					],
					'modified'           => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Date coupon modified', 'wp-graphql-woocommerce' );
				},
					],
					'description'        => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Explanation of what the coupon does', 'wp-graphql-woocommerce' );
				},
					],
					'discountType'       => [
						'type'        => 'DiscountTypeEnum',
						'description' => static function () {
					return __( 'Type of discount', 'wp-graphql-woocommerce' );
				},
					],
					'amount'             => [
						'type'        => 'Float',
						'description' => static function () {
					return __( 'Amount off provided by the coupon', 'wp-graphql-woocommerce' );
				},
					],
					'dateExpiry'         => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Date coupon expires', 'wp-graphql-woocommerce' );
				},
					],
					'usageCount'         => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'How many times the coupon has been used', 'wp-graphql-woocommerce' );
				},
					],
					'individualUse'      => [
						'type'        => 'Boolean',
						'description' => static function () {
					return __( 'Individual use means this coupon cannot be used in conjunction with other coupons', 'wp-graphql-woocommerce' );
				},
					],
					'usageLimit'         => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'Amount of times this coupon can be used globally', 'wp-graphql-woocommerce' );
				},
					],
					'usageLimitPerUser'  => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'Amount of times this coupon can be used by a customer', 'wp-graphql-woocommerce' );
				},
					],
					'limitUsageToXItems' => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'The number of products in your cart this coupon can apply to (for product discounts)', 'wp-graphql-woocommerce' );
				},
					],
					'freeShipping'       => [
						'type'        => 'Boolean',
						'description' => static function () {
					return __( 'Does this coupon grant free shipping?', 'wp-graphql-woocommerce' );
				},
					],
					'excludeSaleItems'   => [
						'type'        => 'Boolean',
						'description' => static function () {
					return __( 'Excluding sale items mean this coupon cannot be used on items that are on sale (or carts that contain on sale items)', 'wp-graphql-woocommerce' );
				},
					],
					'minimumAmount'      => [
						'type'        => 'Float',
						'description' => static function () {
					return __( 'Minimum spend amount that must be met before this coupon can be used', 'wp-graphql-woocommerce' );
				},
					],
					'maximumAmount'      => [
						'type'        => 'Float',
						'description' => static function () {
					return __( 'Maximum spend amount that must be met before this coupon can be used ', 'wp-graphql-woocommerce' );
				},
					],
					'emailRestrictions'  => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
					return __( 'Only customers with a matching email address can use the coupon', 'wp-graphql-woocommerce' );
				},
					],
					'metaData'           => Meta_Data_Type::get_metadata_field_definition(),
				],
			]
		);
	}
}
