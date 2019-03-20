<?php

namespace WPGraphQL\Extensions\WooCommerce;

/**
 * Class Actions
 * 
 * static functions for executing actions on the GraphQL Schema
 * 
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
 */
class Actions
{
  /**
   * Register actions
   */
  public static function load() {
    /**
     * Register WooCommerce post-type fields
     */
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Enum\Backorders', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Enum\CatalogVisibility', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Enum\DiscountType', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Enum\StockStatus', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Enum\TaxStatus', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Object\Coupon', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Connection\Coupons', 'register_connections' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Object\Product', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Type\Object\ProductAttribute', 'register' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Connection\Products', 'register_connections' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Connection\ProductAttributes', 'register_connections' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Connection\ProductGallery', 'register_connections' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Connection\ProductCategories', 'register_connections' ], 10 );
    add_action( 'graphql_register_types', [ '\WPGraphQL\Extensions\WooCommerce\Connection\ProductTags', 'register_connections' ], 10 );
  }
}