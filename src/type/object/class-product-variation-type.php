<?php
/**
 * WPObject Type - Product_Variation_Type
 *
 * Registers Product_Variation WPObject type
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Model\Product_Variation;

/**
 * Class Product_Variation_Type
 */
class Product_Variation_Type {
	/**
	 * Register ProductVariation type to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductVariation',
			array(
				'description'       => __( 'A product variation object', 'wp-graphql-woocommerce' ),
				'interfaces'        => [ WPObjectType::node_interface() ],
				'fields'            => array(
					'id'                => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the product variation', 'wp-graphql-woocommerce' ),
					),
					'variationId'       => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
					),
					'sku'               => array(
						'type'        => 'String',
						'description' => __( 'Product variation SKU (Stock-keeping unit)', 'wp-graphql-woocommerce' ),
					),
					'weight'            => array(
						'type'        => 'String',
						'description' => __( 'Product variation weight', 'wp-graphql-woocommerce' ),
					),
					'length'            => array(
						'type'        => 'String',
						'description' => __( 'Product variation length', 'wp-graphql-woocommerce' ),
					),
					'width'             => array(
						'type'        => 'String',
						'description' => __( 'Product variation width', 'wp-graphql-woocommerce' ),
					),
					'height'            => array(
						'type'        => 'String',
						'description' => __( 'Product variation height', 'wp-graphql-woocommerce' ),
					),
					'taxClass'          => array(
						'type'        => 'String',
						'description' => __( 'Product variation tax class', 'wp-graphql-woocommerce' ),
					),
					'manageStock'       => array(
						'type'        => 'ManageStockEnum',
						'description' => __( 'if/how product variation stock is managed', 'wp-graphql-woocommerce' ),
					),
					'stockQuantity'     => array(
						'type'        => 'Int',
						'description' => __( 'Product variation stock quantity', 'wp-graphql-woocommerce' ),
					),
					'backorders'        => array(
						'type'        => 'String',
						'description' => __( 'Product variation backorders', 'wp-graphql-woocommerce' ),
					),
					'image'             => array(
						'type'        => 'MediaItem',
						'description' => __( 'Product variation main image', 'wp-graphql-woocommerce' ),
					),
					'purchaseNote'      => array(
						'type'        => 'String',
						'description' => __( 'Product variation purchase_note', 'wp-graphql-woocommerce' ),
					),
					'shippingClass'     => array(
						'type'        => 'String',
						'description' => __( 'Product variation shipping class', 'wp-graphql-woocommerce' ),
					),
					'catalogVisibility' => array(
						'type'        => 'String',
						'description' => __( 'Product variation catalog visibility', 'wp-graphql-woocommerce' ),
					),
					'hasAttributes'     => array(
						'type'        => 'Boolean',
						'description' => __( 'Does product variation have any visible attributes', 'wp-graphql-woocommerce' ),
					),
					'isPurchasable'     => array(
						'type'        => 'Boolean',
						'description' => __( 'If product variation can be bought', 'wp-graphql-woocommerce' ),
					),
					'price'             => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s active price', 'wp-graphql-woocommerce' ),
					),
					'regularPrice'      => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s regular price', 'wp-graphql-woocommerce' ),
					),
					'salePrice'         => array(
						'type'        => 'String',
						'description' => __( 'Product variation\'s sale price', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve_node'      => function( $node, $id, $type, $context ) {
					if ( 'product_variation' === $type ) {
						$node = Factory::resolve_crud_object( $id, $context );
					}

					return $node;
				},
				'resolve_node_type' => function( $type, $node ) {
					if ( is_a( $node, Product_Variation::class ) ) {
						$type = 'ProductVariation';
					}

					return $type;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'productVariation',
			array(
				'type'        => 'ProductVariation',
				'description' => __( 'A product variation object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'          => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'Get the product variation by its global ID', 'wp-graphql-woocommerce' ),
					),
					'variationId' => array(
						'type'        => 'Int',
						'description' => __( 'Get the product variation by its database ID', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$variation_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}

						$arg          = 'ID';
						$variation_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['variationId'] ) ) {
						$arg          = 'database ID';
						$variation_id = absint( $args['variationId'] );
					}

					$variation = Factory::resolve_crud_object( $variation_id, $context );
					if ( get_post( $variation_id )->post_type !== 'variation' ) {
						/* translators: not coupon found error message */
						throw new UserError( sprintf( __( 'No product exists with this %1$s: %2$s' ), $arg, $args['id'] ) );
					}

					return $product;
				},
			)
		);
	}
}
