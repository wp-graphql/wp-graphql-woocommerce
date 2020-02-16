<?php
/**
 * WPObjectType - *Product
 *
 * Registers product types and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.3.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Type\WPInterface\Product;

/**
 * Class Product_Types
 */
class Product_Types {

	/**
	 * Registers product types to the WPGraphQL schema
	 */
	public static function register() {
		self::register_simple_product_type();
		self::register_variable_product_type();
		self::register_external_product_type();
		self::register_group_product_type();
	}

	/**
	 * Defines fields related to product inventory.
	 */
	private static function get_inventory_fields() {
		return array(
			'manageStock'       => array(
				'type'        => 'Boolean',
				'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
			),
			'stockQuantity'     => array(
				'type'        => 'Int',
				'description' => __( 'Number of items available for sale', 'wp-graphql-woocommerce' ),
			),
			'backorders'        => array(
				'type'        => 'BackordersEnum',
				'description' => __( 'Product backorders status', 'wp-graphql-woocommerce' ),
			),
			'soldIndividually'  => array(
				'type'        => 'Boolean',
				'description' => __( 'If should be sold individually', 'wp-graphql-woocommerce' ),
			),
			'backordersAllowed' => array(
				'type'        => 'Boolean',
				'description' => __( 'Can product be backordered?', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines fields related to product shipping.
	 */
	private static function get_shipping_fields() {
		return array(
			'weight'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
			),
			'length'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
			),
			'width'            => array(
				'type'        => 'String',
				'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
			),
			'height'           => array(
				'type'        => 'String',
				'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
			),
			'shippingClassId'  => array(
				'type'        => 'Int',
				'description' => __( 'shipping class ID', 'wp-graphql-woocommerce' ),
			),
			'shippingRequired' => array(
				'type'        => 'Boolean',
				'description' => __( 'Does product need to be shipped?', 'wp-graphql-woocommerce' ),
			),
			'shippingTaxable'  => array(
				'type'        => 'Boolean',
				'description' => __( 'Is product shipping taxable?', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines fields not found in grouped-type products.
	 */
	private static function get_non_grouped_fields() {
		return array(
			'price'        => array(
				'type'        => 'String',
				'description' => __( 'Product\'s active price', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'format' => array(
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->priceRaw;
					} else {
						return $source->price;
					}
				},
			),
			'regularPrice' => array(
				'type'        => 'String',
				'description' => __( 'Product\'s regular price', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'format' => array(
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->regularPriceRaw;
					} else {
						// @codingStandardsIgnoreLine.
						return $source->regularPrice;
					}
				},
			),
			'salePrice'    => array(
				'type'        => 'String',
				'description' => __( 'Product\'s sale price', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'format' => array(
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, $args ) {
					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						// @codingStandardsIgnoreLine.
						return $source->salePriceRaw;
					} else {
						// @codingStandardsIgnoreLine.
						return $source->salePrice;
					}
				},
			),
			'taxStatus'    => array(
				'type'        => 'TaxStatusEnum',
				'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
			),
			'taxClass'     => array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'Tax class', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Register "SimpleProduct" type.
	 */
	private static function register_simple_product_type() {
		register_graphql_object_type(
			'SimpleProduct',
			array(
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
					self::get_non_grouped_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields(),
					array(
						'virtual'        => array(
							'type'        => 'Boolean',
							'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
						),
						'downloadExpiry' => array(
							'type'        => 'Int',
							'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
						),
						'downloadable'   => array(
							'type'        => 'Boolean',
							'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
						),
						'downloadLimit'  => array(
							'type'        => 'Int',
							'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
						),
						'downloads'      => array(
							'type'        => array( 'list_of' => 'ProductDownload' ),
							'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
						),
						'stockStatus'    => array(
							'type'        => 'StockStatusEnum',
							'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
						),
					)
				),
			)
		);

		// Register "simpleProduct" query.
		self::register_product_query( 'simple' );
	}

	/**
	 * Registers "VariableProduct" type.
	 */
	private static function register_variable_product_type() {
		register_graphql_object_type(
			'VariableProduct',
			array(
				'description' => __( 'A variable product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
					self::get_non_grouped_fields(),
					self::get_inventory_fields(),
					self::get_shipping_fields()
				),
			)
		);

		// Register "variableProduct" query.
		self::register_product_query( 'variable' );
	}

	/**
	 * Registers "ExternalProduct" type.
	 */
	private static function register_external_product_type() {
		register_graphql_object_type(
			'ExternalProduct',
			array(
				'description' => __( 'A external product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
					self::get_non_grouped_fields(),
					array(
						'externalUrl' => array(
							'type'        => 'String',
							'description' => __( 'External product url', 'wp-graphql-woocommerce' ),
						),
						'buttonText'  => array(
							'type'        => 'String',
							'description' => __( 'External product Buy button text', 'wp-graphql-woocommerce' ),
						),
					)
				),
			)
		);

		// Register "externalProduct" query.
		self::register_product_query( 'external' );
	}

	/**
	 * Registers "GroupProduct" type.
	 */
	public static function register_group_product_type() {
		register_graphql_object_type(
			'GroupProduct',
			array(
				'description' => __( 'A group product object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node', 'Product' ),
				'fields'      => array_merge(
					Product::get_fields(),
					array(
						'addToCartText'        => array(
							'type'        => 'String',
							'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
						),
						'addToCartDescription' => array(
							'type'        => 'String',
							'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
						),
					)
				),
			)
		);

		// Register "groupProduct" query.
		self::register_product_query( 'group' );
	}

	/**
	 * Register product query
	 *
	 * @param string $type  Product type.
	 */
	private static function register_product_query( $type ) {
		$field_name = "{$type}Product";
		$type_name  = ucfirst( $type ) . 'Product';
		register_graphql_field(
			'RootQuery',
			$field_name,
			array(
				'type'        => $type_name,
				'description' => __( 'A simple product object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'        => array(
						'type'        => 'ID',
						'description' => sprintf(
							/* translators: %s: product type */
							__( 'The ID for identifying the %s product', 'wp-graphql-woocommerce' ),
							$type
						),
					),
					'idType'    => array(
						'type'        => 'ProductIdTypeEnum',
						'description' => __( 'Type of ID being used identify product', 'wp-graphql-woocommerce' ),
					),
					/**
					 * DEPRECATED
					 *
					 * Will be removed in v0.5.x.
					 */
					'productId' => array(
						'type'              => 'Int',
						'description'       => __( 'Get the product by its database ID', 'wp-graphql-woocommerce' ),
						'isDeprecated'      => true,
						'deprecationReason' => sprintf(
							/* translators: %s: product type */
							__(
								'This argument has been deprecation, and will be removed in v0.5.x. Please use "%sProduct(id: value, idType: DATABASE_ID)" instead',
								'wp-graphql-woocommerce'
							),
							$type
						),
					),
					/**
					 * DEPRECATED
					 *
					 * Will be removed in v0.5.x.
					 */
					'slug'      => array(
						'type'              => 'String',
						'description'       => __( 'Get the product by its slug', 'wp-graphql-woocommerce' ),
						'isDeprecated'      => true,
						'deprecationReason' => sprintf(
							/* translators: %s: product type */
							__(
								'This argument has been deprecation, and will be removed in v0.5.x. Please use "%sProduct(id: value, idType: SLUG)" instead',
								'wp-graphql-woocommerce'
							),
							$type
						),
					),
					'sku'       => array(
						'type'              => 'String',
						'description'       => __( 'Get the product by its sku', 'wp-graphql-woocommerce' ),
						'isDeprecated'      => true,
						'deprecationReason' => sprintf(
							/* translators: %s: product type */
							__(
								'This argument has been deprecation, and will be removed in v0.5.x. Please use "%sProduct(id: value, idType: SKU)" instead',
								'wp-graphql-woocommerce'
							),
							$type
						),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $type ) {
					$id = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					/**
					 * Process deprecated arguments
					 *
					 * Will be removed in v0.5.x.
					 */
					if ( ! empty( $args['productId'] ) ) {
						$id = $args['productId'];
						$id_type = 'database_id';
					} elseif ( ! empty( $args['slug'] ) ) {
						$id = $args['slug'];
						$id_type = 'slug';
					} elseif ( ! empty( $args['sku'] ) ) {
						$id = $args['sku'];
						$id_type = 'sku';
					}

					$product_id = null;
					switch ( $id_type ) {
						case 'sku':
							$product_id = \wc_get_product_id_by_sku( $id );
							break;
						case 'slug':
							$post = get_page_by_path( $id, OBJECT, 'product' );
							$product_id = ! empty( $post ) ? absint( $post->ID ) : 0;
							break;
						case 'database_id':
							$product_id = absint( $id );
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $id );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
							}
							$product_id = absint( $id_components['id'] );
							break;
					}

					if ( empty( $product_id ) ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $product_id ) );
					} elseif ( \WC()->product_factory->get_product_type( $product_id ) !== $type ) {
						/* translators: Invalid product type message %1$s: Product ID, %2$s: Product type */
						throw new UserError( sprintf( __( 'This product of ID %1$s is not a %2$s product', 'wp-graphql-woocommerce' ), $product_id, $type ) );
					} elseif ( get_post( $product_id )->post_type !== 'product' ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $product_id ) );
					}

					$product = Factory::resolve_crud_object( $product_id, $context );

					return $product;
				},
			)
		);
	}
}
