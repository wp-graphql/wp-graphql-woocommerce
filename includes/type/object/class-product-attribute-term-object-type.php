<?php
/**
 * WPObject Type - Product_Attribute_Term_Object_Type
 *
 * Registers ProductAttributeTermObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Product_Attribute_Term_Object_Type
 */
class Product_Attribute_Term_Object_Type {
	/**
	 * Register ProductAttributeObject type and queries to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductAttributeTermObject',
			[
				'description'     => static function () {
					return __( 'Product attribute object.', 'wp-graphql-woocommerce' );
				},
				'eagerlyLoadType' => true,
				'fields'          => [
					'id'          => [
						'type'        => 'Integer',
						'description' => static function () {
					return __( 'Unique identifier for the product attribute.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					],
					'name'        => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Name of the attribute.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->name ) ? $source->name : null;
						},
					],
					'slug'        => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Label of the attribute.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->slug ) ? $source->slug : null;
						},
					],
					'description' => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'Type of the attribute.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return ! empty( $source->description ) ? $source->description : null;
						},
					],
					'menuOrder'   => [
						'type'        => 'Integer',
						'description' => static function () {
					return __( 'Order by which the attribute should be sorted.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source->menu_order ) ? $source->menu_order : 0;
						},
					],
					'count'       => [
						'type'        => 'Integer',
						'description' => static function () {
					return __( 'Whether or not the attribute has archives.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return isset( $source->count ) ? $source->count : 0;
						},
					],
				],
			]
		);
	}
}
