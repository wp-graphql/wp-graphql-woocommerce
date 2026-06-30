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
		register_graphql_field(
			'RootQuery',
			'product',
			[
				'type'        => 'Product',
				'description' => static function () {
					return __( 'A product object', 'graphql-for-ecommerce' );
				},
				'args'        => [
					'id'     => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The ID for identifying the product', 'graphql-for-ecommerce' );
						},
					],
					'idType' => [
						'type'        => 'ProductIdTypeEnum',
						'description' => static function () {
							return __( 'Type of ID being used identify product', 'graphql-for-ecommerce' );
						},
					],
				],
				'resolve'     => static function ( $source, array $args, AppContext $context ) {
					$id      = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					$product_id = null;
					switch ( $id_type ) {
						case 'sku':
							$product_id = \wc_get_product_id_by_sku( $id );
							break;
						case 'slug':
							$query = new \WP_Query(
								[
									'name'           => $id,
									'post_type'      => 'product',
									'post_status'    => 'publish',
									'posts_per_page' => 1,
									'fields'         => 'ids',
								]
							);
							/** @var int $post_id */
							$post_id    = ! empty( $query->posts ) ? $query->posts[0] : 0;
							$product_id = absint( $post_id );
							break;
						case 'database_id':
							$product_id = absint( $id );
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $id );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "global ID" is invalid', 'graphql-for-ecommerce' ) );
							}
							$product_id = absint( $id_components['id'] );
							break;
					}

					if ( empty( $product_id ) ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product ID was found corresponding to the %1$s: %2$s', 'graphql-for-ecommerce' ), $id_type, $id ) );
					}
					$product = get_post( $product_id );
					if ( ! is_object( $product ) || 'product' !== $product->post_type ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s', 'graphql-for-ecommerce' ), $id_type, $id ) );
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
				'description' => static function () {
					return __( 'Product type', 'graphql-for-ecommerce' );
				},
			],
			'name'              => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product name', 'graphql-for-ecommerce' );
				},
			],
			'featured'          => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'If the product is featured', 'graphql-for-ecommerce' );
				},
			],
			'catalogVisibility' => [
				'type'        => 'CatalogVisibilityEnum',
				'description' => static function () {
					return __( 'Catalog visibility', 'graphql-for-ecommerce' );
				},
			],
			'description'       => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product description', 'graphql-for-ecommerce' );
				},
				'args'        => [
					'format' => [
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => static function () {
							return __( 'Format of the field output', 'graphql-for-ecommerce' );
						},
					],
				],
				'resolve'     => static function ( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->descriptionRaw;
					}
					return $source->description;
				},
			],
			'shortDescription'  => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Product short description', 'graphql-for-ecommerce' );
				},
				'args'        => [
					'format' => [
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => static function () {
							return __( 'Format of the field output', 'graphql-for-ecommerce' );
						},
					],
				],
				'resolve'     => static function ( $source, $args ) {
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
				'description' => static function () {
					return __( 'Product SKU', 'graphql-for-ecommerce' );
				},
			],
			'dateOnSaleFrom'    => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Date on sale from', 'graphql-for-ecommerce' );
				},
			],
			'dateOnSaleTo'      => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Date on sale to', 'graphql-for-ecommerce' );
				},
			],
			'totalSales'        => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Number total of sales', 'graphql-for-ecommerce' );
				},
			],
			'reviewsAllowed'    => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'If reviews are allowed', 'graphql-for-ecommerce' );
				},
			],
			'purchaseNote'      => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Purchase note', 'graphql-for-ecommerce' );
				},
			],
			'menuOrder'         => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Menu order', 'graphql-for-ecommerce' );
				},
			],
			'averageRating'     => [
				'type'        => 'Float',
				'description' => static function () {
					return __( 'Product average count', 'graphql-for-ecommerce' );
				},
			],
			'reviewCount'       => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Product review count', 'graphql-for-ecommerce' );
				},
			],
			'image'             => [
				'type'        => 'MediaItem',
				'description' => static function () {
					return __( 'Main image', 'graphql-for-ecommerce' );
				},
				'resolve'     => static function ( $source, array $args, AppContext $context ) {
					// @codingStandardsIgnoreLine.
					if ( empty( $source->image_id ) || ! absint( $source->image_id ) ) {
						return null;
					}
					return $context->get_loader( 'post' )->load_deferred( $source->image_id );
				},
			],
			'onSale'            => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is product on sale?', 'graphql-for-ecommerce' );
				},
			],
			'purchasable'       => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Can product be purchased?', 'graphql-for-ecommerce' );
				},
			],
			'virtual'           => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is product virtual?', 'graphql-for-ecommerce' );
				},
			],
			'metaData'          => \WPGraphQL\WooCommerce\Type\WPObject\Meta_Data_Type::get_metadata_field_definition(),
		];
	}
}
