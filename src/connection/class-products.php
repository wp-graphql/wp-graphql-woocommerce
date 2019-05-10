<?php
/**
 * Connection - Products
 *
 * Registers connections to Product
 *
 * @package WPGraphQL\Extensions\WooCommerce\Connection
 */

namespace WPGraphQL\Extensions\WooCommerce\Connection;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Products
 */
class Products extends WC_Connection {
	/**
	 * Registers the various connections from other Types to Product
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );
		// From Coupon.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'products',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProducts',
				)
			)
		);
		// From Product.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'related',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'upsell',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'crossSell',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'grouped',
				)
			)
		);

		// From Product to ProductVariation.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'toType'        => 'ProductVariation',
					'fromFieldName' => 'variations',
				)
			)
		);

		// From ProductCategory.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'ProductCategory',
					'fromFieldName' => 'products',
				)
			)
		);

		// From ProductTag.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'ProductTag',
					'fromFieldName' => 'products',
				)
			)
		);

		// From WooCommerce product attributes.
		$attributes = \WP_GraphQL_WooCommerce::get_product_attribute_taxonomies();
		foreach ( $attributes as $attribute ) {
			register_graphql_connection(
				self::get_connection_config(
					array(
						'fromType'      => ucfirst( graphql_format_field_name( $attribute ) ),
						'fromFieldName' => 'products',
					)
				)
			);
			register_graphql_connection(
				self::get_connection_config(
					array(
						'fromType'      => ucfirst( graphql_format_field_name( $attribute ) ),
						'toType'        => 'ProductVariation',
						'fromFieldName' => 'variations',
					)
				)
			);
		}
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @access public
	 * @param array $args - Connection configuration.
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = [] ) {
		$defaults = array(
			'fromType'       => 'RootQuery',
			'toType'         => 'Product',
			'fromFieldName'  => 'products',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function( $id, $args, $context, $info ) {
				return Factory::resolve_crud_object( $id, $context );
			},
			'resolve'        => function ( $source, $args, $context, $info ) {
				return Factory::resolve_product_connection( $source, $args, $context, $info );
			},
		);
		return array_merge( $defaults, $args );
	}

	/**
	 * Returns array of where args
	 *
	 * @return array
	 */
	public static function get_connection_args() {
		$args = array(
			'slug'              => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific slug.', 'wp-graphql-woocommerce' ),
			),
			'status'            => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific status.', 'wp-graphql-woocommerce' ),
			),
			'type'              => array(
				'type'        => 'ProductTypesEnum',
				'description' => __( 'Limit result set to products assigned a specific type.', 'wp-graphql-woocommerce' ),
			),
			'typeIn'            => array(
				'type'        => array( 'list_of' => 'ProductTypesEnum' ),
				'description' => __( 'Limit result set to products assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
			),
			'typeNotIn'         => array(
				'type'        => array( 'list_of' => 'ProductTypesEnum' ),
				'description' => __( 'Limit result set to products not assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
			),
			'sku'               => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'wp-graphql-woocommerce' ),
			),
			'featured'          => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit result set to featured products.', 'wp-graphql-woocommerce' ),
			),
			'categoryName'      => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific category name.', 'wp-graphql-woocommerce' ),
			),
			'categoryNameIn'    => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
			),
			'categoryNameNotIn' => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products not assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
			),
			'category'          => array(
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific category name.', 'wp-graphql-woocommerce' ),
			),
			'categoryIn'        => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
			),
			'categoryNotIn'     => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products not assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
			),
			'tag'               => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific tag name.', 'wp-graphql-woocommerce' ),
			),
			'tagSlugIn'         => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			),
			'tagSlugNotIn'      => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products not assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			),
			'tagId'             => array(
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific tag ID.', 'wp-graphql-woocommerce' ),
			),
			'tagIn'             => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			),
			'tagNotIn'          => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products not assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			),
			'shippingClassId'   => array(
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific shipping class ID.', 'wp-graphql-woocommerce' ),
			),
			'attribute'         => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific attribute. Use the taxonomy name/attribute slug.', 'wp-graphql-woocommerce' ),
			),
			'attributeTerm'     => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific attribute term ID (required an assigned attribute).', 'wp-graphql-woocommerce' ),
			),
			'stockStatus'       => array(
				'type'        => array( 'list_of' => 'StockStatusEnum' ),
				'description' => __( 'Limit result set to products in stock or out of stock.', 'wp-graphql-woocommerce' ),
			),
			'onSale'            => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit result set to products on sale.', 'wp-graphql-woocommerce' ),
			),
			'minPrice'          => array(
				'type'        => 'Float',
				'description' => __( 'Limit result set to products based on a minimum price.', 'wp-graphql-woocommerce' ),
			),
			'maxPrice'          => array(
				'type'        => 'Float',
				'description' => __( 'Limit result set to products based on a maximum price.', 'wp-graphql-woocommerce' ),
			),
			'search'            => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products based on a keyword search.', 'wp-graphql-woocommerce' ),
			),
			'visibility'        => array(
				'type'        => 'CatalogVisibilityEnum',
				'description' => __( 'Limit result set to products with a specific visibility level.', 'wp-graphql-woocommerce' ),
			),
		);

		if ( wc_tax_enabled() ) {
			$args['taxClass'] = array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'Limit result set to products with a specific tax class.', 'wp-graphql-woocommerce' ),
			);
		}

		return array_merge( self::get_shared_connection_args(), $args );
	}
}
