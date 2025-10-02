<?php
/**
 * Connection - Products
 *
 * Registers connections to Product
 *
 * @package WPGraphQL\WooCommerce\Connection
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class - Products
 */
class Products {
	/**
	 * Registers the various connections from other Types to Product
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From RootQuery.
		register_graphql_connection( self::get_connection_config() );

		// From Coupon.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType' => 'Coupon',
					'resolve'  => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
						$resolver->set_query_arg( 'post__in', $source->product_ids );

						return $resolver->get_connection();
					},
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProducts',
					'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
						$resolver->set_query_arg( 'post__in', $source->excluded_product_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				)
			)
		);

		// Connections from all product types to related and upsell.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'       => 'Product',
					'fromFieldName'  => 'related',
					'connectionArgs' => self::get_connection_args(
						array(
							'shuffle' => array(
								'type'        => 'Boolean',
								'description' => __( 'Shuffle results? (Pagination currently not support by this argument)', 'wp-graphql-woocommerce' ),
							),
						)
					),
					'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						// Bypass randomization by default for pagination support.
						if ( empty( $args['where']['shuffle'] ) ) {
							add_filter(
								'woocommerce_product_related_posts_shuffle',
								static function () {
									return false;
								}
							);
						}

						$related_ids = wc_get_related_products( $source->ID, $resolver->get_query_amount() );
						$resolver->set_query_arg( 'post__in', $related_ids );

						return $resolver->get_connection();
					},
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'Product',
					'fromFieldName' => 'upsell',
					'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
						$resolver->set_query_arg( 'post__in', $source->upsell_ids );

						return $resolver->get_connection();
					},
				)
			)
		);

		// Group product children connection.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType' => 'GroupProduct',
					'resolve'  => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
						$resolver->set_query_arg( 'post__in', $source->grouped_ids );

						return $resolver->get_connection();
					},
				)
			)
		);

		// Product cross-sell connections.
		$cross_sell_config = array(
			'fromFieldName' => 'crossSell',
			'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
				$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
				$resolver->set_query_arg( 'post__in', $source->cross_sell_ids );
				return $resolver->get_connection();
			},
		);
		register_graphql_connection(
			self::get_connection_config(
				array_merge( array( 'fromType' => 'SimpleProduct' ), $cross_sell_config )
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array_merge( array( 'fromType' => 'VariableProduct' ), $cross_sell_config )
			)
		);

		register_graphql_connection(
			array(
				'fromType'      => 'Product',
				'toType'        => 'Product',
				'fromFieldName' => 'parent',
				'description'   => __( 'The parent of the node. The parent object can be of various types', 'wp-graphql-woocommerce' ),
				'oneToOne'      => true,
				'queryClass'    => '\WC_Product_Query',
				'resolve'       => static function ( $source, $args, AppContext $context, ResolveInfo $info ) {
					if ( empty( $source->parent_id ) ) {
						return null;
					}

					$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
					$resolver->set_query_arg( 'p', $source->parent_id );

					return $resolver->one_to_one()->get_connection();
				},
			)
		);

		// From WooCommerce product attributes.
		$attributes = WP_GraphQL_WooCommerce::get_product_attribute_taxonomies();
		foreach ( $attributes as $attribute ) {
			register_graphql_connection(
				self::get_connection_config(
					array(
						'fromType'      => ucfirst( graphql_format_field_name( $attribute ) ),
						'toType'        => 'ProductVariation',
						'fromFieldName' => 'variations',
						'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
							$attribute_meta_key = 'attribute_' . strtolower( preg_replace( '/([A-Z])/', '_$1', $source->taxonomyName ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$meta_query         = array(
								'key'     => $attribute_meta_key,
								'value'   => $source->slug,
								'compare' => '=',
							);

							$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
							$resolver->set_query_arg( 'post_type', 'product_variation' );
							$resolver->add_meta_query( $meta_query );

							return $resolver->get_connection();
						},
					)
				)
			);
		}//end foreach
	}

	/**
	 * Returns the singular name of all registered taxonomies connected the products.
	 *
	 * @return array
	 */
	private static function get_product_connected_taxonomies() {
		$taxonomies         = array();
		$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies( 'objects' );

		foreach ( $allowed_taxonomies as $tax_object ) {
			if ( ! in_array( 'product', $tax_object->object_type, true ) ) {
				continue;
			}

			$taxonomies[] = ucfirst( $tax_object->graphql_single_name );
		}

		return $taxonomies;
	}

	/**
	 * Ensures all connection the `Product` type have proper connection config upon registration.
	 *
	 * @param array $config  Connection config.
	 * @return array
	 */
	public static function set_connection_config( $config ) {
		$to_type   = $config['toType'];
		$from_type = $config['fromType'];
		if ( 'Product' === $to_type ) {
			$config['connectionArgs'] = self::get_connection_args();
		}

		$taxonomies = self::get_product_connected_taxonomies();
		if ( 'Product' === $to_type && in_array( $from_type, $taxonomies, true ) ) {
			$config['resolve'] = static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
				$tax_query = array(
					array(
						'taxonomy' => $source->taxonomyName, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'field'    => 'term_id',
						'terms'    => $source->term_id, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'operator' => 'IN',
					),
				);

				$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
				$resolver->add_tax_query( $tax_query );

				return $resolver->get_connection();
			};
		}
		return $config;
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = array() ): array {
		return array_merge(
			array(
				'fromType'         => 'RootQuery',
				'toType'           => 'ProductUnion',
				'fromFieldName'    => 'products',
				'connectionArgs'   => self::get_connection_args(),
				'connectionFields' => self::get_connection_fields(),
				'resolve'          => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

					return $resolver->get_connection();
				},
			),
			$args
		);
	}

	/**
	 * Returns array of edge fields.
	 *
	 * @return array
	 */
	public static function get_connection_fields(): array {
		return array(
			'found' => array(
				'type'        => 'Integer',
				'description' => __( 'Total products founds', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return ! empty( $source['pageInfo']['found'] ) ? $source['pageInfo']['found'] : null;
				},
			),
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @param array $extra_args  Extra connection args.
	 *
	 * @return array
	 */
	public static function get_connection_args( $extra_args = array() ): array {
		$args = array(
			'slugIn'             => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products with specific slugs.', 'wp-graphql-woocommerce' ),
			),
			'status'             => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific status.', 'wp-graphql-woocommerce' ),
			),
			'type'               => array(
				'type'        => 'ProductTypesEnum',
				'description' => __( 'Limit result set to products assigned a specific type.', 'wp-graphql-woocommerce' ),
			),
			'typeIn'             => array(
				'type'        => array( 'list_of' => 'ProductTypesEnum' ),
				'description' => __( 'Limit result set to products assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
			),
			'typeNotIn'          => array(
				'type'        => array( 'list_of' => 'ProductTypesEnum' ),
				'description' => __( 'Limit result set to products not assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
			),
			'sku'                => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'wp-graphql-woocommerce' ),
			),
			'featured'           => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit result set to featured products.', 'wp-graphql-woocommerce' ),
			),
			'category'           => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific category name.', 'wp-graphql-woocommerce' ),
			),
			'categoryIn'         => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
			),
			'categoryNotIn'      => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products not assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
			),
			'categoryId'         => array(
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific category name.', 'wp-graphql-woocommerce' ),
			),
			'categoryIdIn'       => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
			),
			'categoryIdNotIn'    => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products not assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
			),
			'tag'                => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific tag name.', 'wp-graphql-woocommerce' ),
			),
			'tagIn'              => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products assigned to a specific group of tags by name.', 'wp-graphql-woocommerce' ),
			),
			'tagNotIn'           => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'Limit result set to products not assigned to a specific group of tags by name.', 'wp-graphql-woocommerce' ),
			),
			'tagId'              => array(
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific tag ID.', 'wp-graphql-woocommerce' ),
			),
			'tagIdIn'            => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			),
			'tagIdNotIn'         => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'Limit result set to products not assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			),
			'shippingClassId'    => array(
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific shipping class ID.', 'wp-graphql-woocommerce' ),
			),
			'attributes'         => array(
				'type'        => 'ProductAttributeQueryInput',
				'description' => __( 'Limit result set to products with selected global attribute queries.', 'wp-graphql-woocommerce' ),
			),
			'attribute'          => array(
				'type'              => 'String',
				'description'       => __( 'Limit result set to products with a specific global product attribute', 'wp-graphql-woocommerce' ),
				'deprecationReason' => 'Use attributes instead.',
			),
			'attributeTerm'      => array(
				'type'              => 'String',
				'description'       => __( 'Limit result set to products with a specific global product attribute term ID (required an assigned attribute).', 'wp-graphql-woocommerce' ),
				'deprecationReason' => 'Use attributes instead.',
			),
			'stockStatus'        => array(
				'type'        => array( 'list_of' => 'StockStatusEnum' ),
				'description' => __( 'Limit result set to products in stock or out of stock.', 'wp-graphql-woocommerce' ),
			),
			'onSale'             => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit result set to products on sale.', 'wp-graphql-woocommerce' ),
			),
			'minPrice'           => array(
				'type'        => 'Float',
				'description' => __( 'Limit result set to products based on a minimum price.', 'wp-graphql-woocommerce' ),
			),
			'maxPrice'           => array(
				'type'        => 'Float',
				'description' => __( 'Limit result set to products based on a maximum price.', 'wp-graphql-woocommerce' ),
			),
			'search'             => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products based on a keyword search.', 'wp-graphql-woocommerce' ),
			),
			'visibility'         => array(
				'type'        => 'CatalogVisibilityEnum',
				'description' => __( 'Limit result set to products with a specific visibility level.', 'wp-graphql-woocommerce' ),
			),
			'taxonomyFilter'     => array(
				'type'        => 'ProductTaxonomyInput',
				'description' => __( 'Limit result set with complex set of taxonomy filters.', 'wp-graphql-woocommerce' ),
			),
			'orderby'            => array(
				'type'        => array( 'list_of' => 'ProductsOrderbyInput' ),
				'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
			),
			'supportedTypesOnly' => array(
				'type'        => 'Boolean',
				'description' => __( 'Limit result types to types supported by WooGraphQL.', 'wp-graphql-woocommerce' ),
			),
			'includeVariations'  => array(
				'type'        => 'Boolean',
				'description' => __( 'Include variations in the result set.', 'wp-graphql-woocommerce' ),
			),
			'rating'             => array(
				'type'        => array( 'list_of' => 'Integer' ),
				'description' => __( 'Limit result set to products with a specific average rating. Must be between 1 and 5', 'wp-graphql-woocommerce' ),
			),
		);

		if ( wc_tax_enabled() ) {
			$args['taxClass'] = array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'Limit result set to products with a specific tax class.', 'wp-graphql-woocommerce' ),
			);
		}

		return array_merge( get_wc_cpt_connection_args(), $args, $extra_args );
	}
}
