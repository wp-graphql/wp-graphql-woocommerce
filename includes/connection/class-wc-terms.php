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
use WPGraphQL\Connection\TermObjects;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
use WPGraphQL;
use WP_GraphQL_WooCommerce;

/**
 * Class - WC_Terms
 */
class WC_Terms extends TermObjects {

	/**
	 * Registers the various connections from other Types to WooCommerce taxonomies.
	 */
	public static function register_connections() {
		$allowed_taxonomies = WPGraphQL::get_allowed_taxonomies();
		$wc_post_types      = WP_GraphQL_WooCommerce::get_post_types();

		// Loop through the allowed_taxonomies to register appropriate connections
		if ( ! empty( $allowed_taxonomies && is_array( $allowed_taxonomies ) ) ) {
			foreach ( $allowed_taxonomies as $taxonomy ) {
				$tax_object = get_taxonomy( $taxonomy );

				// Registers the connections between each allowed PostObjectType and it's TermObjects
				if ( ! empty( $wc_post_types ) && is_array( $wc_post_types ) ) {
					foreach ( $wc_post_types as $post_type ) {
						if ( in_array( $post_type, $tax_object->object_type, true ) ) {
							$post_type_object = get_post_type_object( $post_type );
							register_graphql_connection(
								self::get_connection_config(
									$tax_object,
									array(
										'fromType'      => $post_type_object->graphql_single_name,
										'toType'        => $tax_object->graphql_single_name,
										'fromFieldName' => $tax_object->graphql_plural_name,
									)
								)
							);
						}
					}
				}
			}
		}

		// From Coupons.
		register_graphql_connection(
			self::get_connection_config(
				get_taxonomy( 'product_cat' ),
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'productCategories',
				)
			)
		);
		register_graphql_connection(
			self::get_connection_config(
				get_taxonomy( 'product_cat' ),
				array(
					'fromType'      => 'Coupon',
					'toType'        => 'ProductCategory',
					'fromFieldName' => 'excludedProductCategories',
				)
			)
		);

		register_graphql_connection(
			array(
				'fromType'       => 'GlobalProductAttribute',
				'toType'         => 'TermNode',
				'queryClass'     => 'WP_Term_Query',
				'resolveNode'    => function( $id, array $args, AppContext $context ) {
					return DataSource::resolve_term_object( $id, $context );
				},
				'fromFieldName'  => 'terms',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					if ( ! $source->is_taxonomy() ) {
						throw new UserError( __( 'Invalid product attribute', 'wp-graphql-woocommerce' ) );
					}

					$taxonomies = array( $source->get_name() );
					$resolver   = new TermObjectConnectionResolver(
						$source,
						$args,
						$context,
						$info,
						$taxonomies
					);

					return $resolver->get_connection();
				},
			)
		);
	}
}
