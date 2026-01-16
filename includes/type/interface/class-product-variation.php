<?php
/**
 * Defines "ProductVariation" interface.
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Connection\Variation_Attribute_Connection_Resolver;
use WPGraphQL\WooCommerce\Type\WPObject\Meta_Data_Type;


/**
 * Class Product_Variation
 */
class Product_Variation {
	/**
	 * Registers the "ProductVariation" interface
	 *
	 * @return void
	 */
	public static function register_interface(): void {
		register_graphql_fields( 'ProductVariation', self::get_fields() );
		register_graphql_connection(
			[
				'fromType'      => 'ProductVariation',
				'toType'        => 'VariationAttribute',
				'fromFieldName' => 'attributes',
				'resolve'       => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Variation_Attribute_Connection_Resolver();

					return $resolver->resolve( $source, $args, $context, $info );
				},
			]
		);
		register_graphql_connection(
			[
				'fromType'      => 'ProductVariation',
				'toType'        => 'Product',
				'fromFieldName' => 'parent',
				'description'   => __( 'The parent of the variation', 'wp-graphql-woocommerce' ),
				'oneToOne'      => true,
				'queryClass'    => '\WC_Product_Query',
				'resolve'       => static function ( $source, $args, AppContext $context, ResolveInfo $info ) {
					if ( empty( $source->parent_id ) ) {
						return null;
					}

					$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
					$resolver->set_query_arg( 'p', $source->parent_id );

					return $resolver->one_to_one()->get_connection();
				},
			]
		);

		register_graphql_object_type(
			'SimpleProductVariation',
			[
				'eagerlyLoadType' => true,
				'model'           => \WPGraphQL\WooCommerce\Model\Product_Variation::class,
				'description'     => __( 'A product variation', 'wp-graphql-woocommerce' ),
				'interfaces'      => [ 'Node', 'ProductVariation' ],
				'fields'          => [],
			]
		);
	}

	/**
	 * Defines fields of "ProductVariation".
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'shippingClass'     => [
				'type'        => 'String',
				'description' => __( 'Product variation shipping class', 'wp-graphql-woocommerce' ),
			],
			'hasAttributes'     => [
				'type'        => 'Boolean',
				'description' => __( 'Does product variation have any visible attributes', 'wp-graphql-woocommerce' ),
			],
		];
	}
}
