<?php

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class ProductAttributes
 *
 * This class organizes the registration of connections to ProductAttributes
 *
 * @package WPGraphQL\Connection
 */
class ProductAttributes {

  /**
   * Registers the various connections from other Types to Coupons
   */
  public static function register_connections() {
    /**
     * Type connections
     */
    register_graphql_connection( self::get_connection_config() );
    register_graphql_connection( self::get_connection_config( [ 'fromFieldName' => 'defaultAttributes' ] ) );
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
      'toType'         => 'ProductAttribute',
      'fromFieldName'  => 'attributes',
      'connectionArgs' => [],
      'resolve'        => function ( $root, $args, $context, $info ) {
        return Factory::resolve_product_attribute_connection( $root, $args, $context, $info );
      },
    ];

    return array_merge( $defaults, $args );
  }

}