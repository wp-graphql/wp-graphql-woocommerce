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
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

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
				[
					'fromType' => 'Coupon',
					'resolve'  => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
						remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

						$resolver->set_query_arg( 'post__in', $source->product_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				]
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProducts',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
						remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

						$resolver->set_query_arg( 'post__in', $source->excluded_product_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				]
			)
		);

		// Connections from all product types to related and upsell.
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'       => 'Product',
					'fromFieldName'  => 'related',
					'connectionArgs' => array_merge(
						self::get_connection_args(),
						[
							'shuffle' => [
								'type'        => 'Boolean',
								'description' => __( 'Shuffle results? (Pagination currently not support by this argument)', 'wp-graphql-woocommerce' ),
							],
						]
					),
					'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
						remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

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

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				]
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'      => 'Product',
					'fromFieldName' => 'upsell',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
						remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

						$resolver->set_query_arg( 'post__in', $source->upsell_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				]
			)
		);

		// Group product children connection.
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType' => 'GroupProduct',
					'resolve'  => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
						remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

						$resolver->set_query_arg( 'post__in', $source->grouped_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				]
			)
		);

		// Product cross-sell connections.
		$cross_sell_config = [
			'fromFieldName' => 'crossSell',
			'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
				add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
				$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
				remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

				$resolver->set_query_arg( 'post__in', $source->cross_sell_ids );

				// Change default ordering.
				if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
					$resolver->set_query_arg( 'orderby', 'post__in' );
				}

				return $resolver->get_connection();
			},
		];
		register_graphql_connection(
			self::get_connection_config(
				array_merge( [ 'fromType' => 'SimpleProduct' ], $cross_sell_config )
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				array_merge( [ 'fromType' => 'VariableProduct' ], $cross_sell_config )
			)
		);

		// From VariableProduct to ProductVariation.
		register_graphql_connection(
			self::get_connection_config(
				[
					'fromType'      => 'VariableProduct',
					'toType'        => 'ProductVariation',
					'fromFieldName' => 'variations',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
						$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
						remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

						$resolver->set_query_arg( 'post_parent', $source->ID );
						$resolver->set_query_arg( 'post_type', 'product_variation' );
						$resolver->set_query_arg( 'post__in', $source->variation_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ), true ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						$resolver = self::set_ordering_query_args( $resolver, $args );

						return $resolver->get_connection();
					},
				]
			)
		);

		register_graphql_connection(
			[
				'fromType'      => 'ProductVariation',
				'toType'        => 'VariableProduct',
				'fromFieldName' => 'parent',
				'description'   => __( 'The parent of the node. The parent object can be of various types', 'wp-graphql-woocommerce' ),
				'oneToOne'      => true,
				'resolve'       => function( $source, $args, AppContext $context, ResolveInfo $info ) {
					if ( empty( $source->parent_id ) ) {
						return null;
					}

					add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
					$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
					remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

					$resolver->set_query_arg( 'p', $source->parent_id );

					$resolver = self::set_ordering_query_args( $resolver, $args );

					return $resolver->one_to_one()->get_connection();
				},
			]
		);

		// Taxonomy To Product resolver.
		$resolve_product_from_taxonomy = function( $source, array $args, AppContext $context, ResolveInfo $info ) {
			add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
			$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
			remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

			$tax_query = [
				[
					// WPCS: slow query ok.
					'taxonomy' => $source->taxonomyName, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					'field'    => 'term_id',
					'terms'    => $source->term_id,
				],
			];
			$resolver->set_query_arg( 'tax_query', $tax_query );

			$resolver = self::set_ordering_query_args( $resolver, $args );

			return $resolver->get_connection();
		};

		// From WooCommerce product attributes.
		$attributes = WP_GraphQL_WooCommerce::get_product_attribute_taxonomies();
		foreach ( $attributes as $attribute ) {
			register_graphql_connection(
				self::get_connection_config(
					[
						'fromType'      => ucfirst( graphql_format_field_name( $attribute ) ),
						'toType'        => 'ProductVariation',
						'fromFieldName' => 'variations',
						'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							global $wpdb;
							$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product_variation' );

							// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$attribute_meta_key = 'attribute_' . strtolower( preg_replace( '/([A-Z])/', '_$1', $source->taxonomyName ) );
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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

							$resolver = self::set_ordering_query_args( $resolver, $args );

							return $resolver->get_connection();
						},
					]
				)
			);
		}//end foreach
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = [] ): array {
		return array_merge(
			[
				'fromType'       => 'RootQuery',
				'toType'         => 'Product',
				'fromFieldName'  => 'products',
				'queryClass'     => '\WC_Product_Query',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					add_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );
					$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );
					remove_filter( 'graphql_post_object_connection_args', [ __CLASS__, 'bypass_get_args_sanitization' ], 10, 3 );

					$resolver = self::set_ordering_query_args( $resolver, $args );

					return $resolver->get_connection();
				},
			],
			$args
		);
	}

	/**
	 * Bypass arg sanization in Post Object Connection Resolver.
	 *
	 * @param array                        $args                Sanitized GraphQL args passed to the resolver.
	 * @param PostObjectConnectionResolver $connection_resolver Instance of the ConnectionResolver.
	 * @param array                        $all_args            array of arguments input in the field as part of the GraphQL query.

	 * @return array
	 */
	public static function bypass_get_args_sanitization( $args, $connection_resolver, $all_args ) {
		return $all_args;
	}

	/**
	 * Undocumented function
	 *
	 * @param PostObjectConnectionResolver $resolver  Connection resolver instance.
	 * @param array                        $args      Connection provided args.
	 *
	 * @return PostObjectConnectionResolver
	 */
	public static function set_ordering_query_args( $resolver, $args ) {
		$backward = isset( $args['last'] ) ? true : false;

		if ( ! empty( $args['where']['orderby'] ) ) {
			foreach ( $args['where']['orderby'] as $orderby_input ) {
				switch ( $orderby_input['field'] ) {
					case '_price':
					case '_regular_price':
					case '_sale_price':
					case '_wc_rating_count':
					case '_wc_average_rating':
					case '_sale_price_dates_from':
					case '_sale_price_dates_to':
					case 'total_sales':
						$order = $orderby_input['order'];

						if ( $backward ) {
							$order = 'ASC' === $order ? 'DESC' : 'ASC';
						}

						$resolver->set_query_arg( 'orderby', [ 'meta_value_num' => $order ] );
						$resolver->set_query_arg( 'meta_key', esc_sql( $orderby_input['field'] ) );
						$resolver->set_query_arg( 'meta_type', 'NUMERIC' );
						break 2;
				}
			}//end foreach
		}//end if

		return $resolver;
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		$args = [
			'slugIn'             => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Limit result set to products with specific slugs.', 'wp-graphql-woocommerce' ),
			],
			'status'             => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific status.', 'wp-graphql-woocommerce' ),
			],
			'type'               => [
				'type'        => 'ProductTypesEnum',
				'description' => __( 'Limit result set to products assigned a specific type.', 'wp-graphql-woocommerce' ),
			],
			'typeIn'             => [
				'type'        => [ 'list_of' => 'ProductTypesEnum' ],
				'description' => __( 'Limit result set to products assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
			],
			'typeNotIn'          => [
				'type'        => [ 'list_of' => 'ProductTypesEnum' ],
				'description' => __( 'Limit result set to products not assigned to a group of specific types.', 'wp-graphql-woocommerce' ),
			],
			'sku'                => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'wp-graphql-woocommerce' ),
			],
			'featured'           => [
				'type'        => 'Boolean',
				'description' => __( 'Limit result set to featured products.', 'wp-graphql-woocommerce' ),
			],
			'category'           => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific category name.', 'wp-graphql-woocommerce' ),
			],
			'categoryIn'         => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Limit result set to products assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
			],
			'categoryNotIn'      => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Limit result set to products not assigned to a group of specific categories by name.', 'wp-graphql-woocommerce' ),
			],
			'categoryId'         => [
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific category name.', 'wp-graphql-woocommerce' ),
			],
			'categoryIdIn'       => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => __( 'Limit result set to products assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
			],
			'categoryIdNotIn'    => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => __( 'Limit result set to products not assigned to a specific group of category IDs.', 'wp-graphql-woocommerce' ),
			],
			'tag'                => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products assigned a specific tag name.', 'wp-graphql-woocommerce' ),
			],
			'tagIn'              => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Limit result set to products assigned to a specific group of tags by name.', 'wp-graphql-woocommerce' ),
			],
			'tagNotIn'           => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Limit result set to products not assigned to a specific group of tags by name.', 'wp-graphql-woocommerce' ),
			],
			'tagId'              => [
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific tag ID.', 'wp-graphql-woocommerce' ),
			],
			'tagIdIn'            => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => __( 'Limit result set to products assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			],
			'tagIdNotIn'         => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => __( 'Limit result set to products not assigned to a specific group of tag IDs.', 'wp-graphql-woocommerce' ),
			],
			'shippingClassId'    => [
				'type'        => 'Int',
				'description' => __( 'Limit result set to products assigned a specific shipping class ID.', 'wp-graphql-woocommerce' ),
			],
			'attribute'          => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific attribute. Use the taxonomy name/attribute slug.', 'wp-graphql-woocommerce' ),
			],
			'attributeTerm'      => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products with a specific attribute term ID (required an assigned attribute).', 'wp-graphql-woocommerce' ),
			],
			'stockStatus'        => [
				'type'        => [ 'list_of' => 'StockStatusEnum' ],
				'description' => __( 'Limit result set to products in stock or out of stock.', 'wp-graphql-woocommerce' ),
			],
			'onSale'             => [
				'type'        => 'Boolean',
				'description' => __( 'Limit result set to products on sale.', 'wp-graphql-woocommerce' ),
			],
			'minPrice'           => [
				'type'        => 'Float',
				'description' => __( 'Limit result set to products based on a minimum price.', 'wp-graphql-woocommerce' ),
			],
			'maxPrice'           => [
				'type'        => 'Float',
				'description' => __( 'Limit result set to products based on a maximum price.', 'wp-graphql-woocommerce' ),
			],
			'search'             => [
				'type'        => 'String',
				'description' => __( 'Limit result set to products based on a keyword search.', 'wp-graphql-woocommerce' ),
			],
			'visibility'         => [
				'type'        => 'CatalogVisibilityEnum',
				'description' => __( 'Limit result set to products with a specific visibility level.', 'wp-graphql-woocommerce' ),
			],
			'taxonomyFilter'     => [
				'type'        => 'ProductTaxonomyInput',
				'description' => __( 'Limit result set with complex set of taxonomy filters.', 'wp-graphql-woocommerce' ),
			],
			'orderby'            => [
				'type'        => [ 'list_of' => 'ProductsOrderbyInput' ],
				'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql-woocommerce' ),
			],
			'supportedTypesOnly' => [
				'type'        => 'Boolean',
				'description' => __( 'Limit result types to types supported by WooGraphQL.', 'wp-graphql-woocommerce' ),
			],
		];

		if ( wc_tax_enabled() ) {
			$args['taxClass'] = [
				'type'        => 'TaxClassEnum',
				'description' => __( 'Limit result set to products with a specific tax class.', 'wp-graphql-woocommerce' ),
			];
		}

		return array_merge( get_wc_cpt_connection_args(), $args );
	}

	/**
	 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
	 * from a GraphQL Query to the WP_Query
	 *
	 * @param array              $query_args The mapped query arguments.
	 * @param array              $args       Query "where" args.
	 * @param mixed              $source     The query results for a query calling this.
	 * @param array              $all_args   All of the arguments for the query (not just the "where" args).
	 * @param AppContext         $context    The AppContext object.
	 * @param ResolveInfo        $info       The ResolveInfo object.
	 * @param mixed|string|array $post_type  The post type for the query.
	 *
	 * @return array Query arguments.
	 */
	public static function map_input_fields_to_wp_query( $query_args, $args, $source, $all_args, $context, $info, $post_type ) {
		if ( ! in_array( 'product', $post_type, true ) && ! in_array( 'product_variation', $post_type, true ) ) {
			return $query_args;
		}

		$where_args = $all_args['where'];
		$query_args = array_merge(
			$query_args,
			map_shared_input_fields_to_wp_query( $where_args )
		);

		$remove = [
			'cat',
			'category_name',
			'category__in',
			'category__not_in',
			'tag_id',
			'tag__and',
			'tag__in',
			'tag__not_in',
		];

		$query_args = array_diff_key( $query_args, array_flip( $remove ) );

		if ( ! empty( $where_args['slugIn'] ) ) {
			$query_args['post_name__in'] = $where_args['slugIn'];
		}

		$tax_query     = [];
		$taxonomy_args = [
			'type'            => 'product_type',
			'typeIn'          => 'product_type',
			'typeNotIn'       => 'product_type',
			'category'        => 'product_cat',
			'categoryIn'      => 'product_cat',
			'categoryNotIn'   => 'product_cat',
			'categoryId'      => 'product_cat',
			'categoryIdIn'    => 'product_cat',
			'categoryIdNotIn' => 'product_cat',
			'tag'             => 'product_tag',
			'tagIn'           => 'product_tag',
			'tagNotIn'        => 'product_tag',
			'tagId'           => 'product_tag',
			'tagIdIn'         => 'product_tag',
			'tagIdNotIn'      => 'product_tag',
		];

		foreach ( $taxonomy_args as $field => $taxonomy ) {
			if ( ! empty( $where_args[ $field ] ) ) {
				// Set tax query operator.
				switch ( true ) {
					case \wc_graphql_ends_with( $field, 'NotIn' ):
						$operator = 'NOT IN';
						break;
					default:
						$operator = 'IN';
						break;
				}

				// Set tax query config.
				switch ( $field ) {
					case 'type':
					case 'typeIn':
					case 'typeNotIn':
					case 'category':
					case 'categoryIn':
					case 'categoryNotIn':
					case 'tag':
					case 'tagIn':
					case 'tagNotIn':
						$tax_query[] = [
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $where_args[ $field ],
							'operator' => $operator,
						];
						break;
					case 'categoryId':
					case 'categoryIdIn':
					case 'categoryIdNotIn':
					case 'tagId':
					case 'tagIdIn':
					case 'tagIdNotIn':
						$tax_query[] = [
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $where_args[ $field ],
							'operator' => $operator,
						];
						break;
				}//end switch
			}//end if
		}//end foreach

		if ( 1 < count( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
		}

		// Filter by attribute and term.
		if ( ! empty( $where_args['attribute'] ) && ! empty( $where_args['attributeTerm'] ) ) {
			if ( in_array( $where_args['attribute'], \wc_get_attribute_taxonomy_names(), true ) ) {
				$tax_query[] = [
					'taxonomy' => $where_args['attribute'],
					'field'    => 'slug',
					'terms'    => $where_args['attributeTerm'],
				];
			}
		}

		if ( empty( $where_args['type'] ) && empty( $where_args['typeIn'] ) && ! empty( $where_args['supportedTypesOnly'] )
			&& true === $where_args['supportedTypesOnly'] ) {
			$supported_types = array_keys( WP_GraphQL_WooCommerce::get_enabled_product_types() );
			$terms           = ! empty( $where_args['typeNotIn'] )
				? array_diff( $supported_types, $where_args['typeNotIn'] )
				: $supported_types;
			$tax_query[]     = [
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $terms,
			];
		}

		if ( isset( $where_args['featured'] ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			if ( $where_args['featured'] ) {
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ $product_visibility_term_ids['featured'] ],
				];
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ $product_visibility_term_ids['exclude-from-catalog'] ],
					'operator' => 'NOT IN',
				];
			} else {
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ $product_visibility_term_ids['featured'] ],
					'operator' => 'NOT IN',
				];
			}
		}//end if

		// Handle visibility.
		$post_type_obj = get_post_type_object( $post_type );
		if ( ! empty( $where_args['visibility'] ) ) {
			switch ( $where_args['visibility'] ) {
				case 'search':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-search' ],
						'operator' => 'NOT IN',
					];
					break;
				case 'catalog':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog' ],
						'operator' => 'NOT IN',
					];
					break;
				case 'visible':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog', 'exclude-from-search' ],
						'operator' => 'NOT IN',
					];
					break;
				case 'hidden':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog', 'exclude-from-search' ],
						'operator' => 'AND',
					];
					break;
			}//end switch
		}//end if

		// Process "taxonomyFilter".
		if ( ! empty( $where_args['taxonomyFilter'] ) ) {
			$taxonomy_query = $where_args['taxonomyFilter'];
			$relation       = ! empty( $taxonomy_query['relation'] ) ? $taxonomy_query['relation'] : 'AND';

			if ( ! empty( $taxonomy_query['filters'] ) ) {
				$tax_groups = [];
				foreach ( $taxonomy_query['filters'] as $filter ) {
					$common = [
						'taxonomy' => $filter['taxonomy'],
						'operator' => ! empty( $filter['operator'] ) ? $filter['operator'] : 'IN',
					];

					if ( ! empty( $filter['ids'] ) ) {
						$tax_groups[] = array_merge(
							$common,
							[
								'field' => 'ID',
								'terms' => $filter['ids'],
							]
						);
					}

					if ( ! empty( $filter['terms'] ) ) {
						$tax_groups[] = array_merge(
							$common,
							[
								'field' => 'slug',
								'terms' => $filter['terms'],
							]
						);
					}
				}//end foreach

				if ( ! empty( $tax_groups ) ) {
					array_push( $tax_query, ...$tax_groups );
				}

				if ( 1 < count( $tax_groups ) ) {
					$tax_query['relation'] = $relation;
				}
			}//end if
		}//end if

		if ( ! empty( $tax_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$query_args['tax_query'] = $tax_query;
		}

		$meta_query = [];
		if ( ! empty( $where_args['sku'] ) ) {
			$meta_query[] = [
				'key'     => '_sku',
				'value'   => $where_args['sku'],
				'compare' => 'LIKE',
			];
		}

		if ( ! empty( $where_args['minPrice'] ) || ! empty( $where_args['maxPrice'] ) ) {
			$current_min_price = isset( $where_args['minPrice'] )
				? floatval( $where_args['minPrice'] )
				: 0;
			$current_max_price = isset( $where_args['maxPrice'] )
				? floatval( $where_args['maxPrice'] )
				: PHP_INT_MAX;

			$meta_query[] = apply_filters(
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				'woocommerce_get_min_max_price_meta_query',
				[
					'key'     => '_price',
					'value'   => [ $current_min_price, $current_max_price ],
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(10,' . wc_get_price_decimals() . ')',
				],
				$query_args
			);
		}

		if ( isset( $where_args['stockStatus'] ) ) {
			$meta_query[] = [
				'key'     => '_stock_status',
				'value'   => $where_args['stockStatus'],
				'compare' => is_array( $where_args['stockStatus'] ) ? 'IN' : '=',
			];
		}

		if ( ! empty( $meta_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$query_args['meta_query'] = $meta_query;
		}

		if ( ! empty( $where_args['onSale'] ) && is_bool( $where_args['onSale'] ) ) {
			$on_sale_key = $where_args['onSale'] ? 'post__in' : 'post__not_in';
			$on_sale_ids = \wc_get_product_ids_on_sale();

			$on_sale_ids                = empty( $on_sale_ids ) ? [ 0 ] : $on_sale_ids;
			$query_args[ $on_sale_key ] = $on_sale_ids;
		}

		/**
		 * Filter the input fields
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the WP_Query
		 *
		 * @param array       $args       The mapped query arguments
		 * @param array       $where_args Query "where" args
		 * @param mixed       $source     The query results for a query calling this
		 * @param array       $all_args   All of the arguments for the query (not just the "where" args)
		 * @param AppContext  $context    The AppContext object
		 * @param ResolveInfo $info       The ResolveInfo object
		 * @param mixed|string|array      $post_type  The post type for the query
		 */
		$query_args = apply_filters_deprecated(
			'graphql_map_input_fields_to_product_query',
			[
				$query_args,
				$where_args,
				$source,
				$args,
				$context,
				$info,
				$post_type,
			],
			'0.9.0',
			'graphql_map_input_fields_to_wp_query'
		);

		return $query_args;
	}

}
