<?php
/**
 * WPInterface Type - Product
 *
 * Registers Product interface.
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.3.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class - Product
 */
class Product {

	/**
	 * Registers the "Product" interface.
	 *
	 * @return void
	 */
	public static function register_interface() {

		// Register the fields to the Product Interface
		// the product interface is defined by the post_type registration.
		register_graphql_fields( 'Product', self::get_fields() );

		register_graphql_field(
			'RootQuery',
			'product',
			[
				'type'        => 'Product',
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'args'        => [
					'id'     => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The ID for identifying the product', 'wp-graphql-woocommerce' ),
					],
					'idType' => [
						'type'        => 'ProductIdTypeEnum',
						'description' => __( 'Type of ID being used identify product', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => function ( $source, array $args, AppContext $context ) {
					$id      = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					$product_id = null;
					switch ( $id_type ) {
						case 'sku':
							$product_id = \wc_get_product_id_by_sku( $id );
							break;
						case 'slug':
							$post       = get_page_by_path( $id, OBJECT, 'product' );
							$product_id = ! empty( $post ) ? absint( $post->ID ) : 0;
							break;
						case 'database_id':
							$product_id = absint( $id );
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $id );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "global ID" is invalid', 'wp-graphql-woocommerce' ) );
							}
							$product_id = absint( $id_components['id'] );
							break;
					}

					if ( empty( $product_id ) ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					}
					$product = get_post( $product_id );
					if ( ! is_object( $product ) || 'product' !== $product->post_type ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					}

					return Factory::resolve_crud_object( $product_id, $context );
				},
			]
		);
	}

	/**
	 * Defines Product fields. All child type must have these fields as well.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'type'              => [
				'type'        => 'ProductTypesEnum',
				'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
			],
			'name'              => [
				'type'        => 'String',
				'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
			],
			'featured'          => [
				'type'        => 'Boolean',
				'description' => __( 'If the product is featured', 'wp-graphql-woocommerce' ),
			],
			'catalogVisibility' => [
				'type'        => 'CatalogVisibilityEnum',
				'description' => __( 'Catalog visibility', 'wp-graphql-woocommerce' ),
			],
			'description'       => [
				'type'        => 'String',
				'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->descriptionRaw;
					}
					return $source->description;
				},
			],
			'shortDescription'  => [
				'type'        => 'String',
				'description' => __( 'Product short description', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->shortDescriptionRaw;
					}
					// @codingStandardsIgnoreLine.
					return $source->shortDescription;
				},
			],
			'sku'               => [
				'type'        => 'String',
				'description' => __( 'Product SKU', 'wp-graphql-woocommerce' ),
			],
			'dateOnSaleFrom'    => [
				'type'        => 'String',
				'description' => __( 'Date on sale from', 'wp-graphql-woocommerce' ),
			],
			'dateOnSaleTo'      => [
				'type'        => 'String',
				'description' => __( 'Date on sale to', 'wp-graphql-woocommerce' ),
			],
			'totalSales'        => [
				'type'        => 'Int',
				'description' => __( 'Number total of sales', 'wp-graphql-woocommerce' ),
			],
			'reviewsAllowed'    => [
				'type'        => 'Boolean',
				'description' => __( 'If reviews are allowed', 'wp-graphql-woocommerce' ),
			],
			'purchaseNote'      => [
				'type'        => 'String',
				'description' => __( 'Purchase note', 'wp-graphql-woocommerce' ),
			],
			'menuOrder'         => [
				'type'        => 'Int',
				'description' => __( 'Menu order', 'wp-graphql-woocommerce' ),
			],
			'averageRating'     => [
				'type'        => 'Float',
				'description' => __( 'Product average count', 'wp-graphql-woocommerce' ),
			],
			'reviewCount'       => [
				'type'        => 'Int',
				'description' => __( 'Product review count', 'wp-graphql-woocommerce' ),
			],
			'image'             => [
				'type'        => 'MediaItem',
				'description' => __( 'Main image', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context ) {
					// @codingStandardsIgnoreLine.
					if ( empty( $source->image_id ) || ! absint( $source->image_id ) ) {
						return null;
					}
					return $context->get_loader( 'post' )->load_deferred( $source->image_id );
				},
			],
			'onSale'            => [
				'type'        => 'Boolean',
				'description' => __( 'Is product on sale?', 'wp-graphql-woocommerce' ),
			],
			'purchasable'       => [
				'type'        => 'Boolean',
				'description' => __( 'Can product be purchased?', 'wp-graphql-woocommerce' ),
			],
			'metaData'          => \WPGraphQL\WooCommerce\Type\WPObject\Meta_Data_Type::get_metadata_field_definition(),
		];
	}
}
