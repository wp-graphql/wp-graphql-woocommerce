<?php
/**
 * WPObject Type - Variation_Attribute_Type
 *
 * Registers VariationAttribute WPObject type
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.4
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
 * Class Variation_Attribute_Type
 */
class Variation_Attribute_Type {
	/**
	 * Register VariationAttribute type to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'VariationAttribute',
			array(
				'description' => __( 'A product variation attribute object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'    => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['id'] ) ? $source['id'] : null;
						},
					),
					'name'  => array(
						'type'        => 'String',
						'description' => __( 'Name of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['name'] ) ? $source['name'] : null;
						},
					),
					'value' => array(
						'type'        => 'String',
						'description' => __( 'Selected value of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['value'] ) ? $source['value'] : null;
						},
					),
				),
			)
		);
	}
}
