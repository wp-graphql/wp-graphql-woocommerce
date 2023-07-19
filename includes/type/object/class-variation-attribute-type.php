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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'VariationAttribute',
			[
				'description' => __( 'A product variation attribute object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Attribute' ],
				'fields'      => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The Global ID of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source['id'] ) ? $source['id'] : null;
						},
					],
					'attributeId' => [
						'type'        => 'Int',
						'description' => __( 'The Database ID of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source['attributeId'] ) ? $source['attributeId'] : null;
						},
					],
					'label'       => [
						'type'        => 'String',
						'description' => __( 'Label of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							if ( ! isset( $source['name'] ) ) {
								return null;
							}

							$slug  = \wc_attribute_taxonomy_slug( $source['name'] );
							$label = preg_replace( '/(-|_)/', ' ', $slug );
							return ! empty( $label ) ? ucwords( $label ) : null;
						},
					],
					'name'        => [
						'type'        => 'String',
						'description' => __( 'Name of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source['name'] ) ? $source['name'] : null;
						},
					],
					'value'       => [
						'type'        => 'String',
						'description' => __( 'Selected value of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source['value'] ) ? $source['value'] : null;
						},
					],
				],
			]
		);
	}
}
