<?php
/**
 * WPObject Type - Variation_Attribute_Type
 *
 * Registers VariationAttribute WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.4
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

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
					'id'          => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The Global ID of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['id'] ) ? $source['id'] : null;
						},
					),
					'attributeId' => array(
						'type'        => 'Int',
						'description' => __( 'The Database ID of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['attributeId'] ) ? $source['attributeId'] : null;
						},
					),
					'label'       => array(
						'type'        => 'String',
						'description' => __( 'Label of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							if ( ! isset( $source['name'] ) ) {
								return null;
							}

							$slug = \wc_attribute_taxonomy_slug( $source['name'] );
							return ucwords( str_replace( '_', ' ', $slug ) );
						},
					),
					'name'        => array(
						'type'        => 'String',
						'description' => __( 'Name of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['name'] ) ? $source['name'] : null;
						},
					),
					'value'       => array(
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
