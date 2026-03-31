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
				'description' => static function () {
					return __( 'A product variation attribute object', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Attribute' ],
				'fields'      => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
					return __( 'The Global ID of the attribute.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source['id'] ) ? $source['id'] : null;
						},
					],
					'attributeId' => [
						'type'        => 'Int',
						'description' => static function () {
					return __( 'The Database ID of the attribute.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source['attributeId'] ) ? $source['attributeId'] : null;
						},
					],
					'label'       => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Label of attribute', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source['label'] ) ? $source['label'] : null;
						},
					],
					'name'        => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Name of attribute', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source['name'] ) ? $source['name'] : null;
						},
					],
					'value'       => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Selected value of attribute', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source['value'] ) ? $source['value'] : null;
						},
					],
				],
			]
		);
	}
}
