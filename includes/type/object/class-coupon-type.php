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
					return __( 'A coupon object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'                 => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The globally unique identifier for the coupon', 'graphql-for-ecommerce' );
						},
					],
					'databaseId'         => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'The ID of the coupon in the database', 'graphql-for-ecommerce' );
						},
					],
					'code'               => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Coupon code', 'graphql-for-ecommerce' );
						},
					],
					'date'               => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Date coupon created', 'graphql-for-ecommerce' );
						},
					],
					'modified'           => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Date coupon modified', 'graphql-for-ecommerce' );
						},
					],
					'description'        => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Explanation of what the coupon does', 'graphql-for-ecommerce' );
						},
					],
					'discountType'       => [
						'type'        => 'DiscountTypeEnum',
						'description' => static function () {
							return __( 'Type of discount', 'graphql-for-ecommerce' );
						},
					],
					'amount'             => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Amount off provided by the coupon', 'graphql-for-ecommerce' );
						},
					],
					'dateExpiry'         => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Date coupon expires', 'graphql-for-ecommerce' );
						},
					],
					'usageCount'         => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'How many times the coupon has been used', 'graphql-for-ecommerce' );
						},
					],
					'individualUse'      => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Individual use means this coupon cannot be used in conjunction with other coupons', 'graphql-for-ecommerce' );
						},
					],
					'usageLimit'         => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Amount of times this coupon can be used globally', 'graphql-for-ecommerce' );
						},
					],
					'usageLimitPerUser'  => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Amount of times this coupon can be used by a customer', 'graphql-for-ecommerce' );
						},
					],
					'limitUsageToXItems' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'The number of products in your cart this coupon can apply to (for product discounts)', 'graphql-for-ecommerce' );
						},
					],
					'freeShipping'       => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Does this coupon grant free shipping?', 'graphql-for-ecommerce' );
						},
					],
					'excludeSaleItems'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Excluding sale items mean this coupon cannot be used on items that are on sale (or carts that contain on sale items)', 'graphql-for-ecommerce' );
						},
					],
					'minimumAmount'      => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Minimum spend amount that must be met before this coupon can be used', 'graphql-for-ecommerce' );
						},
					],
					'maximumAmount'      => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Maximum spend amount that must be met before this coupon can be used ', 'graphql-for-ecommerce' );
						},
					],
					'emailRestrictions'  => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Only customers with a matching email address can use the coupon', 'graphql-for-ecommerce' );
						},
					],
					'metaData'           => Meta_Data_Type::get_metadata_field_definition(),
				],
			]
		);
	}
}
