<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Object;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Data\DataSource;
use GraphQLRelay\Relay;

/**
 * Class ProductAttribute
 * 
 * Registers proper ProductAttribute type and query
 * 
 * @package \WPGraphQL\Extensions\WooCommerce\Type\Object
 * @since   0.0.1
 */
class ProductAttribute {
  public static function register() {
    /**
     * Register Product Type
     */
    register_graphql_object_type( 'ProductAttribute', [
      'description' => __( 'A product attribute object', 'wp-graphql-woocommerce' ),
      'fields'      => [
        'id'  => [
          'type' => [
            'non_null' => 'ID'
          ],
          'resolve' => function( $attribute ) {
            return ! empty( $attribute ) ? Relay::toGlobalId( 'productAttribute', $attribute->get_id() ) : null; 
          }
        ],
        'attributeId' => [
          'type'        => [
            'non_null' => 'Int'
          ],
          'description' => __( 'Attribute ID', 'wp-graphql-woocommerce' ),
          'resolve'     => function( $attribute ) {
            return $attribute->get_id(); 
          }
        ],
        'name' => [
          'type'        => [
            'non_null' => 'String'
          ],
          'description' => __( 'Attribute name', 'wp-graphql-woocommerce' ),
          'resolve'     => function( $attribute ) {
            return $attribute->get_name(); 
          }
        ],
        'options' => [
          'type'        => [
            'list_of' => 'String'
          ],
          'description' => __( 'Attribute options', 'wp-graphql-woocommerce' ),
          'resolve'     => function( $attribute ) {
            if ( ! $attribute->is_taxonomy() || ! taxonomy_exists( $attribute->get_name() ) ) {
              return null;
            }
            $options = [];
            foreach ( $attribute->get_options() as $option ) {
              $options[] = get_term_by( 'id', $option, $attribute->get_name() )->name;
            }
            return $options;
          },
        ],
        'position' => [
          'type'        => [
            'non_null' => 'Int'
          ],
          'description' => __('Attribute position', 'wp-graphql-woocommerce'),
          'resolve'     => function( $attribute ) {
            return $attribute->get_position(); 
          }
        ],
        'visible' => [
          'type'        => [
            'non_null' => 'Boolean'
          ],
          'description' => __('Is attribute visible', 'wp-graphql-woocommerce'),
          'resolve'     => function( $attribute ) {
            return $attribute->get_visible(); 
          }
        ],
        'variation' => [
          'type'        => [
            'non_null' => 'Boolean'
          ],
          'description' => __('Is attribute on product variation', 'wp-graphql-woocommerce'),
          'resolve'     => function( $attribute ) {
            return $attribute->get_variation(); 
          }
        ],
      ]
    ] );
  }
}