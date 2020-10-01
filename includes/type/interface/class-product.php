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
use WPGraphQL\Data\DataSource;
use WPGraphQL\WooCommerce\Data\Factory;
use WP_GraphQL_WooCommerce;

/**
 * Class - Product
 */
class Product {

	/**
	 * Registers the "Product" interface.
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry  Instance of the WPGraphQL TypeRegistry.
	 */
	public static function register_interface( &$type_registry ) {
		register_graphql_interface_type(
			'Product',
			array(
				'description' => __( 'Product object', 'wp-graphql-woocommerce' ),
				'fields'      => self::get_fields(),
				'resolveType' => function( $value ) use ( &$type_registry ) {
					$possible_types = WP_GraphQL_WooCommerce::get_enabled_product_types();
					if ( isset( $possible_types[ $value->type ] ) ) {
						return $type_registry->get_type( $possible_types[ $value->type ] );
					}
					throw new UserError(
						sprintf(
							/* translators: %s: Product type */
							__( 'The "%s" product type is not supported by the core WPGraphQL WooCommerce (WooGraphQL) schema.', 'wp-graphql-woocommerce' ),
							$value->type
						)
					);
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'product',
			array(
				'type'        => 'Product',
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'     => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The ID for identifying the product', 'wp-graphql-woocommerce' ),
					),
					'idType' => array(
						'type'        => 'ProductIdTypeEnum',
						'description' => __( 'Type of ID being used identify product', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context ) {
					$id = isset( $args['id'] ) ? $args['id'] : null;
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
					} elseif ( get_post( $product_id )->post_type !== 'product' ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					}

					return Factory::resolve_crud_object( $product_id, $context );
				},
			)
		);
	}

	/**
	 * Defines product fields. All child type must have these fields as well.
	 */
	public static function get_fields() {
		return array(
			'id'                => array(
				'type'        => array( 'non_null' => 'ID' ),
				'description' => __( 'The globally unique identifier for the product', 'wp-graphql-woocommerce' ),
			),
			'databaseId'        => array(
				'type'        => array( 'non_null' => 'Int' ),
				'description' => __( 'The ID of the product in the database', 'wp-graphql-woocommerce' ),
			),
			'slug'              => array(
				'type'        => 'String',
				'description' => __( 'Product slug', 'wp-graphql-woocommerce' ),
			),
			'date'              => array(
				'type'        => 'String',
				'description' => __( 'Date product created', 'wp-graphql-woocommerce' ),
			),
			'modified'          => array(
				'type'        => 'String',
				'description' => __( 'Date product last updated', 'wp-graphql-woocommerce' ),
			),
			'type'              => array(
				'type'        => 'ProductTypesEnum',
				'description' => __( 'Product type', 'wp-graphql-woocommerce' ),
			),
			'name'              => array(
				'type'        => 'String',
				'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
			),
			'status'            => array(
				'type'        => 'String',
				'description' => __( 'Product status', 'wp-graphql-woocommerce' ),
			),
			'featured'          => array(
				'type'        => 'Boolean',
				'description' => __( 'If the product is featured', 'wp-graphql-woocommerce' ),
			),
			'catalogVisibility' => array(
				'type'        => 'CatalogVisibilityEnum',
				'description' => __( 'Catalog visibility', 'wp-graphql-woocommerce' ),
			),
			'description'       => array(
				'type'        => 'String',
				'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'format' => array(
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->descriptionRaw;
					}
					return $source->description;
				},
			),
			'shortDescription'  => array(
				'type'        => 'String',
				'description' => __( 'Product short description', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'format' => array(
						'type'        => 'PostObjectFieldFormatEnum',
						'description' => __( 'Format of the field output', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->shortDescriptionRaw;
					}
					// @codingStandardsIgnoreLine.
					return $source->shortDescription;
				},
			),
			'sku'               => array(
				'type'        => 'String',
				'description' => __( 'Product SKU', 'wp-graphql-woocommerce' ),
			),
			'dateOnSaleFrom'    => array(
				'type'        => 'String',
				'description' => __( 'Date on sale from', 'wp-graphql-woocommerce' ),
			),
			'dateOnSaleTo'      => array(
				'type'        => 'String',
				'description' => __( 'Date on sale to', 'wp-graphql-woocommerce' ),
			),
			'totalSales'        => array(
				'type'        => 'Int',
				'description' => __( 'Number total of sales', 'wp-graphql-woocommerce' ),
			),
			'reviewsAllowed'    => array(
				'type'        => 'Boolean',
				'description' => __( 'If reviews are allowed', 'wp-graphql-woocommerce' ),
			),
			'purchaseNote'      => array(
				'type'        => 'String',
				'description' => __( 'Purchase note', 'wp-graphql-woocommerce' ),
			),
			'menuOrder'         => array(
				'type'        => 'Int',
				'description' => __( 'Menu order', 'wp-graphql-woocommerce' ),
			),
			'averageRating'     => array(
				'type'        => 'Float',
				'description' => __( 'Product average count', 'wp-graphql-woocommerce' ),
			),
			'reviewCount'       => array(
				'type'        => 'Int',
				'description' => __( 'Product review count', 'wp-graphql-woocommerce' ),
			),
			'parent'            => array(
				'type'        => 'Product',
				'description' => __( 'Parent product', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context ) {
					return Factory::resolve_crud_object( $source->parent_id, $context );
				},
			),
			'image'             => array(
				'type'        => 'MediaItem',
				'description' => __( 'Main image', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context ) {
					// @codingStandardsIgnoreLine.
					if ( empty( $source->image_id ) || ! absint( $source->image_id ) ) {
						return null;
					}
					return DataSource::resolve_post_object( $source->image_id, $context );
				},
			),
			'onSale'            => array(
				'type'        => 'Boolean',
				'description' => __( 'Is product on sale?', 'wp-graphql-woocommerce' ),
			),
			'purchasable'       => array(
				'type'        => 'Boolean',
				'description' => __( 'Can product be purchased?', 'wp-graphql-woocommerce' ),
			),
			'link'              => array(
				'type'        => 'String',
				'description' => __( 'The permalink of the post', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source ) {
					$permalink = get_post_permalink( $source->ID );
					return ! empty( $permalink ) ? $permalink : null;
				},
			),
		);
	}
}
