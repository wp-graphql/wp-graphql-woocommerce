<?php
/**
 * WPObject Type - Collection_Stats_Type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WPGraphQL\Data\Connection\TermObjectConnectionResolver;

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
			'PriceRangeStats',
			[
				'eagerlyLoadType' => true,
				'description' => __( 'Price range stats', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'minPrice' => [
						'type'        => 'Float',
						'description' => __( 'Minimum price', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['min_price'] ) ? $source['min_price'] : null;
						}
					],
					'maxPrice' => [
						'type'        => 'Float',
						'description' => __( 'Maximum price', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return ! empty( $source['max_price'] ) ? $source['max_price'] : null;
						}
					],
				],
			]
		);

		register_graphql_object_type (
			'AttributeCount',
			[
				'eagerlyLoadType' => true,
				'description' => __( 'Attribute count', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'termId' => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Term ID', 'wp-graphql-woocommerce' ),
					],
					'count' => [
						'type'        => 'Int',
						'description' => __( 'Filtered Term Count', 'wp-graphql-woocommerce' ),
					],
				],
				'connections' => [
					'term'     => [
						'toType'         => 'TermNode',
						'oneToOne'       => true,
						'resolve'        => static function ( $source, $args, $context, $info ) {
							$term_id  = ! empty ( $source->termId ) ? $source->termId : 0;
							$taxonomy = ! empty ( $source->taxonomy ) ? $source->taxonomy : null;
							$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $taxonomy );

							return $resolver->one_to_one()
								->set_query_arg( 'include', [ $term_id ] )
								->get_connection();
						},
					],
				]
			]
		);

		register_graphql_object_type(
			'RatingCount',
			[
				'eagerlyLoadType' => true,
				'description' => __( 'Rating count', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'rating' => [
						'type'        => [ 'non_null' => 'Int' ],
						'description' => __( 'Rating', 'wp-graphql-woocommerce' ),
					],
					'count' => [
						'type'        => 'Int',
						'description' => __( 'Filtered Rating Count', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_object_type(
			'CollectionStats',
			[
				'description' => __( '', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'priceRange' => [
						'type'        => 'PriceRangeStats',
						'description' => __( 'Price range', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							$min_price = ! empty( $source['min_price'] ) ? $source['min_price'] : null;
							$max_price = ! empty( $source['max_price'] ) ? $source['max_price'] : null;
							return compact( 'min_price', 'max_price' );
						}
					],
					'attributeCounts' => [
						'type'        => [ 'list_of' => 'AttributeCount' ],
						'args'        => [
							'page' => [
								'type'        => 'Int',
								'description' => __( 'Page of results to return.', 'wp-graphql-woocommerce' ),
							],
							'perPage' => [
								'type'        => 'Int',
								'description' => __( 'Number of results to return per page.', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Attribute counts', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source, $args ) {
							$page = ! empty( $args['page'] ) ? $args['page'] : 1;
							$per_page = ! empty( $args['perPage'] ) ? $args['perPage'] : 0;
							$attribute_counts = ! empty( $source['attribute_counts'] ) ? $source['attribute_counts'] : [];
							$attribute_counts = array_slice(
								$attribute_counts,
								( $page - 1 ) * $per_page,
								0 < $per_page ? $per_page : null
							);
				
							return $attribute_counts;
						}
					],
					'ratingCounts' => [
						'type'        => [ 'list_of' => 'RatingCount' ],
						'args'        => [
							'page' => [
								'type'        => 'Int',
								'description' => __( 'Page of results to return.', 'wp-graphql-woocommerce' ),
							],
							'perPage' => [
								'type'        => 'Int',
								'description' => __( 'Number of results to return per page.', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Rating counts', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source, $args ) {
							$page = ! empty( $args['page'] ) ? $args['page'] : 1;
							$per_page = ! empty( $args['perPage'] ) ? $args['perPage'] : 0;
							$rating_counts = ! empty( $source['rating_counts'] ) ? $source['rating_counts'] : [];
							$rating_counts = array_slice(
								$rating_counts,
								( $page - 1 ) * $per_page,
								0 < $per_page ? $per_page : null
							);
				
							return $rating_counts;
						}
					],
				],
			]
		);
	}
}
