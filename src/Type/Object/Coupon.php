<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Object;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use GraphQLRelay\Relay;

/**
 * Class Coupon
 * 
 * Registers proper Coupon type and query
 * 
 * @package \WPGraphQL\Extensions\WooCommerce\Type\Object
 * @since   0.0.1
 */
class Coupon
{
  public static function register() {
    /**
     * Register Coupon Type
     */
    register_graphql_object_type( 'Coupon', [
      'description' => __( 'A coupon object', 'wp-graphql-woocommerce' ),
      'fields'      => [
        'id'  => [
          'type' => [
            'non_null' => 'ID'
          ],
          'resolve' => function( $coupon ) {
            return ! empty( $coupon ) ? Relay::toGlobalId( 'coupon', $coupon->get_id() ) : null; 
          }
        ],
        'couponId' => [
          'type'        => [
            'non_null' => 'Int'
          ],
          'description' => __('Coupon ID', 'wp-graphql-woocommerce'),
          'resolve'     => function( $coupon ) {
            return $coupon->get_id(); 
          }
        ],
        'code' => [
          'type'        => 'String',
          'description' => __('Coupon code', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_code();
          }
        ],
        'date' => [
          'type'        => 'String',
          'description' => __('Date coupon was created', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_date_created();
          }
        ],
        'modified' => [
          'type'        => 'String',
          'description' => __('Date coupon was last modified', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_date_modified();
          }
        ],
        'description' => [
          'type'        => 'String',
          'description' => __('Explanation of what the coupon does', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_description();
          }
        ],
        'discountType' => [
          'type'        => 'DiscountTypeEnum',
          'description' => __('Type of discount', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_discount_type();
          }
        ],
        'amount' => [
          'type'        => 'Float',
          'description' => __('Amount off provided by the coupon', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_amount();
          }
        ],
        'dateExpiry' => [
          'type'        => 'String',
          'description' => __('Date coupon expires', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_date_expires();
          }
        ],
        'usageCount' => [
          'type'        => 'Int',
          'description' => __('How many times the coupon has been used', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_usage_count();
          }
        ],
        'individualUse' => [
          'type'        => 'Boolean',
          'description' => __('Individual use means this coupon cannot be used in conjunction with other coupons', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_individual_use();
          }
        ],
        'usageLimit' => [
          'type'        => 'Int',
          'description' => __('Amount of times this coupon can be used globally', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_usage_limit();
          }
        ],
        'usageLimitPerUser' => [
          'type'        => 'Int',
          'description' => __('Amount of times this coupon can be used by a customer', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_usage_limit_per_user();
          }
        ],
        'limitUsageToXItems' => [
          'type'        => 'Int',
          'description' => __('The number of products in your cart this coupon can apply to (for product discounts)', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_limit_usage_to_x_items();
          }
        ],
        'freeShipping' => [
          'type'        => 'Boolean',
          'description' => __('Does this coupon grant free shipping?', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_free_shipping();
          }
        ],
        'excludeSaleItems' => [
          'type'        => 'Boolean',
          'description' => __('Excluding sale items mean this coupon cannot be used on items that are on sale (or carts that contain on sale items)', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_exclude_sale_items();
          }
        ],
        'minimumAmount' => [
          'type'        => 'Float',
          'description' => __('Minimum spend amount that must be met before this coupon can be used', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_minimun_amount();
          }
        ],
        'maximumAmount' => [
          'type'        => 'Float',
          'description' => __('Maximum spend amount that must be met before this coupon can be used ', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_maximum_amount();
          }
        ],
        'emailRestrictions' => [
          'type'        => [ 'list_of' => 'String' ],
          'description' => __('Only customers with a matching email address can use the coupon', 'wp-graphql-woocommerce'),
          'resolve'     => function ($coupon) {
            return $coupon->get_email_restrictions();
          }
        ],
      ]
    ]);

    /**
     * Register coupon queries
     */
    register_graphql_field( 'RootQuery', 'coupon', [
      'type'        => 'Coupon',
      'description' => __('A Coupon object', 'wp-graphql-woocommerce'),
      'args'        => [
        'id' => [
          'type' => [
            'non_null' => 'ID',
          ],
        ],
      ],
      'resolve'     => function ($source, array $args, $context, $info) {
          $id_components = Relay::fromGlobalId($args['id']);
          return Factory::resolve_coupon($id_components['id']);
      },
    ] );

    register_graphql_field( 'RootQuery', 'couponBy', [
      'type'        => 'Coupon',
      'description' => __('A Coupon object', 'wp-graphql-woocommerce'),
      'args'        => [
        'couponId' => [
          'type' => 'Int',
        ],
        'code' => [
          'type' => 'String'
        ]
      ],
      'resolve'     => function ($source, array $args, $context, $info) {
          if ( ! empty( $args['couponId'] ) ) {
            return Factory::resolve_coupon( $args['couponId'] );
          }
          if ( ! empty( $args['code'] ) ) {
            return Factory::resolve_coupon( $args['code'] );
          }
          return null;
      },
    ] );
  }
}