<?php
/**
 * WPObject Type - Product_Attribute_Object_Type
 *
 * Registers ProductAttributeObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Product_Attribute_Object_Type
 */
class Product_Attribute_Object_Type {
	/**
	 * Register ProductAttributeObject type and queries to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductAttributeObject',
			[
				'description'     => __( 'Product attribute object.', 'wp-graphql-woocommerce' ),
				'eagerlyLoadType' => true,
				'fields'          => [
					'id'          => [
						'type'        => 'ID',
						'description' => __( 'Unique identifier for the product attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->attribute_id ) ? $source->attribute_id : null;
						},
					],
					'name'        => [
						'type'        => 'String',
						'description' => __( 'Name of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->attribute_name ) ? (string) $source->attribute_name : null;
						},
					],
					'label'       => [
						'type'        => 'String',
						'description' => __( 'Label of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->attribute_label ) ? (string) $source->attribute_label : null;
						},
					],
					'type'        => [
						'type'        => 'String',
						'description' => __( 'Type of the attribute.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->attribute_type ) ? (string) $source->attribute_type : null;
						},
					],
					'orderBy'     => [
						'type'        => 'String',
						'description' => __( 'Order by which the attribute should be sorted.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->attribute_orderby ) ? (string) $source->attribute_orderby : null;
						},
					],
					'hasArchives' => [
						'type'        => 'Boolean',
						'description' => __( 'Whether or not the attribute has archives.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source->attribute_public ) ? $source->attribute_public : false;
						},
					],
				],
			]
		);
	}
}
