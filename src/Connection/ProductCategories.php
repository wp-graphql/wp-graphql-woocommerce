<?php

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class ProductCategories
 *
 * This class organizes the registration of connections to ProductCategories
 *
 * @package WPGraphQL\Connection
 */
class ProductCategories {

  /**
   * Registers the various connections from other Types to Coupons
   */
  public static function register_connections() {
    /**
     * Type connections
     */
    register_graphql_connection( self::get_connection_config() );
    register_graphql_connection( self::get_connection_config( [
      'fromType'  => 'Coupon',
      'fromFieldName' => 'productCategories',
    ] ) );
    register_graphql_connection( self::get_connection_config( [
      'fromType'  => 'Coupon',
      'fromFieldName' => 'excludedProductCategories',
    ] ) );
  }

  /**
   * Given an array of $args, this returns the connection config, merging the provided args
   * with the defaults
   *
   * @access public
   * @param array $args
   *
   * @return array
   */
  public static function get_connection_config( $args = [] ) {
    $defaults = [
      'fromType'       => 'Product',
      'toType'         => 'ProductCategory',
      'fromFieldName'  => 'categories',
      'connectionArgs' => [],
      'resolve'        => function ( $root, $args, $context, $info ) {
        return Factory::resolve_product_category_connection( $root, $args, $context, $info );
      },
    ];

    return array_merge( $defaults, $args );
  }

}