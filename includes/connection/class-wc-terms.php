<?php
/**
 * Connection type - WC taxonomies.
 *
 * Registers connections to WC taxonomy types.
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Error\UserError;
use WPGraphQL\AppContext;
use WPGraphQL\Type\Connection\TermObjects;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
use WPGraphQL;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class - WC_Terms
 */
class WC_Terms extends TermObjects {

	/**
	 * Registers the various connections from other Types to WooCommerce taxonomies.
	 *
	 * @throws \Exception If the "product_cat" taxonomy is not found.
	 *
	 * @return void
	 */
	public static function register_connections() {
		/**
		 * Get the allowed taxonomies.
		 *
		 * @var array<string,\WP_Taxonomy> $allowed_taxonomies
		 */
		$allowed_taxonomies = WPGraphQL::get_allowed_taxonomies( 'objects' );
		$wc_post_types      = WP_GraphQL_WooCommerce::get_post_types();

		// Loop through the allowed_taxonomies to register appropriate connections.
		foreach ( $allowed_taxonomies as $taxonomy => $tax_object ) {
			foreach ( $wc_post_types as $post_type ) {
				if ( 'product' === $post_type ) {
					continue;
				}

				if ( in_array( $post_type, $tax_object->object_type, true ) ) {
					$post_type_object = get_post_type_object( $post_type );

					if ( null === $post_type_object ) {
						continue;
					}

					register_graphql_connection(
						self::get_connection_config(
							$tax_object,
							[
								'fromType'      => $post_type_object->graphql_single_name,
								'toType'        => $tax_object->graphql_single_name,
								'fromFieldName' => $tax_object->graphql_plural_name,
								'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $tax_object ) {
									$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $tax_object->name );

									$term_ids = \wc_get_object_terms( $source->ID, $tax_object->name, 'term_id' );

									$resolver->set_query_arg( 'term_taxonomy_id', ! empty( $term_ids ) ? $term_ids : [ '0' ] );

									return $resolver->get_connection();
								},
							]
						)
					);
				}//end if
			}//end foreach
		}//end foreach

		// From Coupons to ProductCategory connections.
		$tax_object = get_taxonomy( 'product_cat' );
		if ( ! $tax_object ) {
			throw new \Exception( __( '"product_cat" taxonomy not found', 'wp-graphql-woocommerce' ) );
		}

		register_graphql_connection(
			self::get_connection_config(
				$tax_object,
				[
					'fromType'      => 'Coupon',
					'fromFieldName' => 'productCategories',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $tax_object ) {
						$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $tax_object->name );
						$resolver->set_query_arg( 'term_taxonomy_id', $source->product_category_ids );

						return $resolver->get_connection();
					},
				]
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				$tax_object,
				[
					'fromType'      => 'Coupon',
					'fromFieldName' => 'excludedProductCategories',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $tax_object ) {
						$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $tax_object->name );
						$resolver->set_query_arg( 'term_taxonomy_id', $source->excluded_product_category_ids );

						return $resolver->get_connection();
					},
				]
			)
		);

		register_graphql_connection(
			[
				'fromType'       => 'GlobalProductAttribute',
				'toType'         => 'TermNode',
				'queryClass'     => 'WP_Term_Query',
				'fromFieldName'  => 'terms',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					if ( ! $source->is_taxonomy() ) {
						throw new UserError( __( 'Invalid product attribute', 'wp-graphql-woocommerce' ) );
					}

					$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $source->get_name() );
					$resolver->set_query_arg( 'slug', $source->get_slugs() );
					return $resolver->get_connection();
				},
			]
		);
	}
}
