<?php
/**
 * WPObject Type - Collection_Stats_Type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.18.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Collection_Stats_Type
 */
class Collection_Stats_Type {
	/**
	 * Register CollectionStats type to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'PriceRange',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'Price range', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'minPrice' => [
						'type'        => 'String',
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Minimum price', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args ) {
							if ( empty( $source['min_price'] ) ) {
								return null;
							}

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $source['min_price'];
							}

							return wc_graphql_price( $source['min_price'] );
						},
					],
					'maxPrice' => [
						'type'        => 'String',
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Maximum price', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args ) {
							if ( empty( $source['max_price'] ) ) {
								return null;
							}

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $source['max_price'];
							}

							return wc_graphql_price( $source['max_price'] );
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'AttributeCount',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'Product attribute terms count', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'slug'  => [
						'type'        => [ 'non_null' => 'ProductAttributeEnum' ],
						'description' => __( 'Attribute name', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return $source->name;
						},
					],
					'label' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Attribute taxonomy', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$taxonomy = get_taxonomy( $source->name );

							if ( ! $taxonomy instanceof \WP_Taxonomy ) {
								return null;
							}
							return $taxonomy->label;
						},
					],
					'name'  => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Attribute name', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$taxonomy = get_taxonomy( $source->name );

							if ( ! $taxonomy instanceof \WP_Taxonomy ) {
								return null;
							}
							return $taxonomy->labels->singular_name;
						},
					],
					'terms' => [
						'type'        => [ 'list_of' => 'SingleAttributeCount' ],
						'description' => __( 'Attribute terms', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_object_type(
			'SingleAttributeCount',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'Single attribute term count', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'termId' => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Term ID', 'wp-graphql-woocommerce' ),
					],
					'count'  => [
						'type'        => 'Int',
						'description' => __( 'Number of products.', 'wp-graphql-woocommerce' ),
					],
					'node'   => [
						'type'        => 'TermNode',
						'description' => __( 'Term object.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							if ( empty( $source->termId ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
								return null;
							}

							/**
							 * Term object.
							 *
							 * @var \WP_Term $term
							 */
							$term = get_term( $source->termId ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							if ( ! $term instanceof \WP_Term ) {
								return null;
							}
							return new \WPGraphQL\Model\Term( $term );
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'RatingCount',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'Single rating count', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'rating' => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => __( 'Average rating', 'wp-graphql-woocommerce' ),
					],
					'count'  => [
						'type'        => 'Int',
						'description' => __( 'Number of products', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_object_type(
			'StockStatusCount',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'Single stock status count', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'status' => [
						'type'        => [ 'non_null' => 'StockStatusEnum' ],
						'description' => __( 'Status', 'wp-graphql-woocommerce' ),
					],
					'count'  => [
						'type'        => 'Int',
						'description' => __( 'Number of products.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_object_type(
			'CollectionStats',
			[
				'description' => __( 'Data about a collection of products', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'priceRange'        => [
						'type'        => 'PriceRange',
						'description' => __( 'Min and max prices found in collection of products, provided using the smallest unit of the currency', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$min_price = ! empty( $source['min_price'] ) ? $source['min_price'] : null;
							$max_price = ! empty( $source['max_price'] ) ? $source['max_price'] : null;
							return compact( 'min_price', 'max_price' );
						},
					],
					'attributeCounts'   => [
						'type'        => [ 'list_of' => 'AttributeCount' ],
						'args'        => [
							'page'    => [
								'type'        => 'Int',
								'description' => __( 'Page of results to return', 'wp-graphql-woocommerce' ),
							],
							'perPage' => [
								'type'        => 'Int',
								'description' => __( 'Number of results to return per page', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Returns number of products within attribute terms', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, $args ) {
							$page             = ! empty( $args['page'] ) ? $args['page'] : 1;
							$per_page         = ! empty( $args['perPage'] ) ? $args['perPage'] : 0;
							$attribute_counts = ! empty( $source['attribute_counts'] ) ? $source['attribute_counts'] : [];
							$attribute_counts = array_slice(
								$attribute_counts,
								( $page - 1 ) * $per_page,
								0 < $per_page ? $per_page : null
							);

							return array_map(
								static function ( $name, $terms ) {
									return (object) compact( 'name', 'terms' );
								},
								array_keys( $attribute_counts ),
								array_values( $attribute_counts )
							);
						},
					],
					'ratingCounts'      => [
						'type'        => [ 'list_of' => 'RatingCount' ],
						'args'        => [
							'page'    => [
								'type'        => 'Int',
								'description' => __( 'Page of results to return', 'wp-graphql-woocommerce' ),
							],
							'perPage' => [
								'type'        => 'Int',
								'description' => __( 'Number of results to return per page', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Returns number of products with each average rating', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, $args ) {
							$page          = ! empty( $args['page'] ) ? $args['page'] : 1;
							$per_page      = ! empty( $args['perPage'] ) ? $args['perPage'] : 0;
							$rating_counts = ! empty( $source['rating_counts'] ) ? $source['rating_counts'] : [];
							$rating_counts = array_slice(
								$rating_counts,
								( $page - 1 ) * $per_page,
								0 < $per_page ? $per_page : null
							);

							return $rating_counts;
						},
					],
					'stockStatusCounts' => [
						'type'        => [ 'list_of' => 'StockStatusCount' ],
						'args'        => [
							'page'    => [
								'type'        => 'Int',
								'description' => __( 'Page of results to return', 'wp-graphql-woocommerce' ),
							],
							'perPage' => [
								'type'        => 'Int',
								'description' => __( 'Number of results to return per page', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Returns number of products with each stock status', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, $args ) {
							$page                = ! empty( $args['page'] ) ? $args['page'] : 1;
							$per_page            = ! empty( $args['perPage'] ) ? $args['perPage'] : 0;
							$stock_status_counts = ! empty( $source['stock_status_counts'] ) ? $source['stock_status_counts'] : [];
							$stock_status_counts = array_slice(
								$stock_status_counts,
								( $page - 1 ) * $per_page,
								0 < $per_page ? $per_page : null
							);

							return $stock_status_counts;
						},
					],
				],
			]
		);
	}

	/**
	 * Prepare the WP_Rest_Request instance used for the resolution of a
	 * statistics for a product connection.
	 *
	 * @param array $where_args  Arguments used to filter the connection results.
	 *
	 * @return \WP_REST_Request
	 */
	public static function prepare_rest_request( array $where_args = [] ) {
		$request = new \WP_REST_Request();
		if ( empty( $where_args ) ) {
			return $request;
		}

		$request->set_param( 'paged', 0 );
		$request->set_param( 'ignore_sticky_posts', true );

		$key_mapping = [
			'slugIn'       => 'slug',
			'typeIn'       => 'type',
			'categoryIdIn' => 'category',
			'tagIdIn'      => 'tag',
			'onSale'       => 'on_sale',
			'stockStatus'  => 'stock_status',
			'visibility'   => 'catalog_visibility',
			'minPrice'     => 'min_price',
			'maxPrice'     => 'max_price',
		];

		$needs_formatting = [ 'attributes', 'categoryIn', 'tagIn' ];
		foreach ( $where_args as $key => $value ) {
			if ( in_array( $key, $needs_formatting, true ) ) {
				continue;
			}

			$request->set_param( $key_mapping[ $key ] ?? $key, $value );
		}

		if ( ! empty( $where_args['categoryIn'] ) ) {
			$category_ids = array_map(
				static function ( $category ) {
					$term = get_term_by( 'slug', $category, 'product_cat' );
					if ( $term && ! is_wp_error( $term ) ) {
						return $term->term_id;
					}
					return 0;
				},
				$where_args['categoryIn']
			);
			$set_category = $request->get_param( 'category' );
			if ( ! empty( $set_category ) ) {
				$category_ids[] = $set_category;
				$request->set_param( 'category', $category_ids );
			} else {
				$request->set_param( 'category', $category_ids );
			}
		}

		if ( ! empty( $where_args['tagIn'] ) ) {
			$tag_ids = array_map(
				static function ( $tag ) {
					$term = get_term_by( 'slug', $tag, 'product_tag' );
					if ( $term && ! is_wp_error( $term ) ) {
						return $term->term_id;
					}
					return 0;
				},
				$where_args['tagIn']
			);
			$set_tag = $request->get_param( 'tag' );
			if ( ! empty( $set_tag ) ) {
				$tag_ids[] = $set_tag;
				$request->set_param( 'tag', $tag_ids );
			} else {
				$request->set_param( 'tag', $tag_ids );
			}
		}

		if ( ! empty( $where_args['attributes'] ) && ! empty( $where_args['attributes']['queries'] ) ) {
			$attributes       = $where_args['attributes']['queries'];
			$att_queries      = [];
			$operator_mapping = [
				'IN'     => 'in',
				'NOT IN' => 'not_in',
				'AND'    => 'and',
			];

			foreach ( $attributes as $filter ) {
				if ( empty( $filter['terms'] ) && empty( $filter['ids'] ) ) {
					continue;
				}

				$operator = ! empty( $filter['operator'] ) ? $operator_mapping[ $filter['operator'] ] : 'in';
				if ( ! empty( $filter['terms'] ) ) {
					foreach ( $filter['terms'] as $term ) {
						$att_queries[] = [
							'attribute' => $filter['taxonomy'],
							'operator'  => $operator,
							'slug'      => $term,
						];
					}
				}

				if ( ! empty( $filter['ids'] ) ) {
					foreach ( $filter['ids'] as $term_id ) {
						$att_queries[] = [
							'attribute' => $filter['taxonomy'],
							'operator'  => $operator,
							'term_id'   => $term_id,
						];
					}
				}
			}

			if ( ! empty( $att_queries ) ) {
				$request->set_param( 'attributes', $att_queries );
			}

			if ( ! empty( $where_args['attributes']['relation'] ) ) {
				$relation = $where_args['attributes']['relation'];
				if ( 'NOT_IN' === $relation ) {
					graphql_debug( __( 'NOT_IN relation is not supported for attributes queries top-level "relation" field. Use "IN" or "AND" instead.', 'wp-graphql-woocommerce' ) );
					$relation = 'IN';
				}
				$request->set_param( 'attributes_relation', $where_args['attributes']['relation'] );
			}
		}//end if

		return $request;
	}
}
