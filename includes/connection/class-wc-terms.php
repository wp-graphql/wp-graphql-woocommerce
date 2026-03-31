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

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
use WPGraphQL\Type\Connection\TermObjects;

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
					'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $tax_object ) {
						$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $tax_object->name );

						$term_taxonomy_ids = self::get_term_taxonomy_ids( $source->product_category_ids, $tax_object->name );
						$resolver->set_query_arg( 'term_taxonomy_id', $term_taxonomy_ids );

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
					'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $tax_object ) {
						$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, $tax_object->name );

						$term_taxonomy_ids = self::get_term_taxonomy_ids( $source->excluded_product_category_ids, $tax_object->name );
						$resolver->set_query_arg( 'term_taxonomy_id', $term_taxonomy_ids );

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
				'connectionArgs' => self::get_connection_args(
					[
						'orderby' => [
							'type'        => 'ProductAttributesConnectionOrderbyEnum',
							'description' => static function () {
								return __( 'Field(s) to order terms by. Defaults to \'name\'.', 'wp-graphql-woocommerce' );
							},
						],
					]
				),
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
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

	/**
	 * Converts an array of term_ids to their corresponding term_taxonomy_ids
	 * for a given taxonomy.
	 *
	 * WooCommerce stores term_ids on coupons, but WP_Term_Query's
	 * term_taxonomy_id arg requires term_taxonomy_ids. These can differ
	 * when terms are shared across taxonomies.
	 *
	 * @param array<int|string> $term_ids Term IDs to convert.
	 * @param string            $taxonomy Taxonomy name.
	 *
	 * @return array<int> Term taxonomy IDs.
	 */
	private static function get_term_taxonomy_ids( array $term_ids, string $taxonomy ): array {
		if ( empty( $term_ids ) || [ '0' ] === $term_ids || [ 0 ] === $term_ids ) {
			return [ 0 ];
		}

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'include'    => array_map( 'absint', $term_ids ),
				'fields'     => 'tt_ids',
				'hide_empty' => false,
			]
		);

		return ! empty( $terms ) && ! is_wp_error( $terms ) ? array_map( 'intval', $terms ) : [ 0 ];
	}
}
