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
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Products
 */
class Products {

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
					'fromType' => 'Coupon',
					'resolve'  => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post__in', $source->product_ids );

						// Change default ordering
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

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
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post__in', $source->excluded_product_ids );

						// Change default ordering
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
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
					'connectionArgs' => array_merge(
						self::get_connection_args(),
						array(
							'shuffle' => array(
								'type'        => 'Boolean',
								'description' => __( 'Shuffle results? (Pagination currently not support by this argument)', 'wp-graphql-woocommerce' ),
							)
						)
					),
					'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						// Bypass randomization by default for pagination support.
						if ( empty( $args['where']['shuffle'] ) ) {
							add_filter(
								'woocommerce_product_related_posts_shuffle',
								function() {
									return false;
								}
							);
						}

						$related_ids = wc_get_related_products( $source->ID, $resolver->get_query_amount() );
						$resolver->set_query_arg( 'post__in', $related_ids );

						// Change default ordering
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

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
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post__in', $source->upsell_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

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
					'resolve'  => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post__in', $source->grouped_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					}
				)
			)
		);

		// Product cross-sell connections.
		$cross_sell_config = array(
			'fromFieldName' => 'crossSell',
			'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
				$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

				$resolver->set_query_arg( 'post__in', $source->cross_sell_ids );

				// Change default ordering.
				if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
					$resolver->set_query_arg( 'orderby', 'post__in' );
				}

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

		// From VariableProduct to ProductVariation.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'      => 'VariableProduct',
					'toType'        => 'ProductVariation',
					'fromFieldName' => 'variations',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post_parent', $source->ID );
						$resolver->set_query_arg( 'post_type', 'product_variation' );
						$resolver->set_query_arg( 'post__in', $source->variation_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				)
			)
		);

		register_graphql_connection(
			array(
				'fromType'      => 'ProductVariation',
				'toType'        => 'VariableProduct',
				'fromFieldName' => 'parent',
				'description'   => __( 'The parent of the node. The parent object can be of various types', 'wp-graphql' ),
				'oneToOne'      => true,
				'resolve'       => function( $source, $args, AppContext $context, ResolveInfo $info ) {

					if ( empty( $source->parent_id ) ) {
						return null;
					}

					$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
					$resolver->set_query_arg( 'p', $source->parent_id );

					return $resolver->one_to_one()->get_connection();

				},
			)
		);

		// Taxonomy To Product resolver.
		$resolve_product_from_taxonomy = function( $source, array $args, AppContext $context, ResolveInfo $info ) {
			$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

			$tax_query = array(
				array( // WPCS: slow query ok.
					'taxonomy' => $source->taxonomyName,
					'field'    => 'term_id',
					'terms'    => $source->term_id,
				)
			);
			$resolver->set_query_arg( 'tax_query', $tax_query );

			return $resolver->get_connection();
		};

		// From ProductCategory.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType' => 'ProductCategory',
					'resolve'  => $resolve_product_from_taxonomy,
				)
			)
		);

		// From ProductTag.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType' => 'ProductTag',
					'resolve'  => $resolve_product_from_taxonomy,
				)
			)
		);

		// From WooCommerce product attributes.
		$attributes = \WP_GraphQL_WooCommerce::get_product_attribute_taxonomies();
		foreach ( $attributes as $attribute ) {
			register_graphql_connection(
				self::get_connection_config(
					array(
						'fromType' => ucfirst( graphql_format_field_name( $attribute ) ),
						'resolve'  => $resolve_product_from_taxonomy,
					)
				)
			);
			register_graphql_connection(
				self::get_connection_config(
					array(
						'fromType'      => ucfirst( graphql_format_field_name( $attribute ) ),
						'toType'        => 'ProductVariation',
						'fromFieldName' => 'variations',
						'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							global $wpdb;
							$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

							$attribute_meta_key = 'attribute_' . strtolower( preg_replace( '/([A-Z])/', '_$1', $source->taxonomyName ) );
							$variation_ids = $wpdb->get_col(
								$wpdb->prepare(
									"SELECT ID
									FROM {$wpdb->prefix}posts
									WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND meta_value = %s)
									AND post_type = 'product_variation'",
									$attribute_meta_key,
									$source->slug
								)
							);

							$resolver->set_query_arg( 'post__in', $variation_ids );
							$resolver->set_query_arg( 'post_type', 'product_variation' );

							return $resolver->get_connection();
						},
					)
				)
			);
		}
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
				'fromType'       => 'RootQuery',
				'toType'         => 'Product',
				'fromFieldName'  => 'products',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					return Factory::resolve_product_connection( $source, $args, $context, $info );
				},
			),
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		$args = array(
			'slug'               => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific slug.', 'wp-graphql-woocommerce' ),
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
			'attribute'          => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific attribute. Use the taxonomy name/attribute slug.', 'wp-graphql-woocommerce' ),
			),
			'attributeTerm'      => array(
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific attribute term ID (required an assigned attribute).', 'wp-graphql-woocommerce' ),
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
				'type'        => array( 'list_of' => 'ProductTaxonomyFilterRelationInput' ),
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
		);

		if ( wc_tax_enabled() ) {
			$args['taxClass'] = array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'Limit result set to products with a specific tax class.', 'wp-graphql-woocommerce' ),
			);
		}

		return array_merge( get_wc_cpt_connection_args(), $args );
	}
}
