<?php
/**
 * WPObject Type - Cart_Type
 *
 * Registers Cart WPObject type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.3
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPUnion;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\TypeRegistry;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Product_Union
 */
class Product_Union {
	/**
	 * Registers ProductUnion.
	 */
	public static function register_union() {
		$possible_types = array_map(
			function( $type_name ) {
				return TypeRegistry::get_type( $type_name );
			},
			\WP_GraphQL_WooCommerce::get_enabled_product_types()
		);

		register_graphql_union_type(
			'ProductUnion',
			[
				'name'        => 'ProductUnion',
				'types'       => array_values( $possible_types ),
				'resolveType' => function( $value ) use ( $possible_types ) {
					if ( isset( $possible_types[ $value->type ] ) ) {
						return $possible_types[ $value->type ];
					}
					return null;
				},
			]
		);

		register_graphql_field(
			'RootQuery',
			'product',
			array(
				'type'        => 'ProductUnion',
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id' => array(
						'type' => array( 'non_null' => 'ID' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );
					if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}
					$product_id = absint( $id_components['id'] );
					return Factory::resolve_crud_object( $product_id, $context );
				},
			)
		);

		$post_by_args = array(
			'id'        => array(
				'type'        => 'ID',
				'description' => __( 'Get the product by its global ID', 'wp-graphql-woocommerce' ),
			),
			'productId' => array(
				'type'        => 'Int',
				'description' => __( 'Get the product by its database ID', 'wp-graphql-woocommerce' ),
			),
			'slug'      => array(
				'type'        => 'String',
				'description' => __( 'Get the product by its slug', 'wp-graphql-woocommerce' ),
			),
			'sku'       => array(
				'type'        => 'String',
				'description' => __( 'Get the product by its sku', 'wp-graphql-woocommerce' ),
			),
		);

		register_graphql_field(
			'RootQuery',
			'productBy',
			array(
				'type'        => 'ProductUnion',
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'args'        => $post_by_args,
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$product_id = 0;
					$id_type = '';
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}
						$product_id = absint( $id_components['id'] );
						$id_type = 'ID';
					} elseif ( ! empty( $args['productId'] ) ) {
						$product_id = absint( $args['productId'] );
						$id_type = 'product ID';
					} elseif ( ! empty( $args['slug'] ) ) {
						$post       = get_page_by_path( $args['slug'], OBJECT, 'product' );
						$product_id = ! empty( $post ) ? absint( $post->ID ) : 0;
						$id_type = 'slug';
					} elseif ( ! empty( $args['sku'] ) ) {
						$product_id = \wc_get_product_id_by_sku( $args['sku'] );
						$id_type = 'sku';
					}

					if ( empty( $product_id ) ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product ID was found corresponding to the %1$s: %2$s' ), $id_type, $product_id ) );
					} elseif ( get_post( $product_id )->post_type !== 'product' ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s' ), $id_type, $product_id ) );
					}

					$product = Factory::resolve_crud_object( $product_id, $context );

					return $product;
				},
			)
		);
	}
}
