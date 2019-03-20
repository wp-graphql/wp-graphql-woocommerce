<?php

namespace WPGraphQL\Extensions\WooCommerce;

/**
 * Class Filters
 * 
 * static functions for executing actions on the GraphQL Schema
 * 
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
 */
class Filters
{
  /**
   * Register filters
   */
  public static function load() {
    /**
     * Filter Connections query info
     */
    add_filter( 'graphql_connection_query_info', [
      '\WPGraphQL\Extensions\WooCommerce\Data\CouponConnectionResolver',
      'query_info_filter'
    ], 10, 2 );
    add_filter( 'graphql_connection_query_info', [
      '\WPGraphQL\Extensions\WooCommerce\Data\ProductConnectionResolver',
      'query_info_filter'
    ], 10, 2 );

    /**
     * Filter Connection query args
     */
    add_filter( 'graphql_post_object_connection_query_args', [
      '\WPGraphQL\Extensions\WooCommerce\Data\GalleryConnectionQueryArgs',
      'query_info_filter'
    ], 10, 4 );
  }
}