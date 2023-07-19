<?php
/**
 * WPObject Types - LocalProductAttribute && GlobalProductAttribute
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQLRelay\Relay;

/**
 * Class Product_Attribute_Types
 */
class Product_Attribute_Types {
	/**
	 * Registers ProductAttribute types
	 *
	 * @return void
	 */
	public static function register() {
		// Local.
		register_graphql_object_type(
			'LocalProductAttribute',
			[
				'description' => __( 'A product attribute object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'ProductAttribute' ],
				'fields'      => [
					'id'    => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Attribute Global ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $attribute ) {
							return ! empty( $attribute->_relay_id ) ? $attribute->_relay_id : Relay::toGlobalId( 'LocalProductAttribute', $attribute->get_id() );
						},
					],
					'scope' => [
						'type'        => [ 'non_null' => 'ProductAttributeTypesEnum' ],
						'description' => __( 'Product attribute scope.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function () {
							return 'local';
						},
					],
				],
			]
		);

		// Global.
		register_graphql_object_type(
			'GlobalProductAttribute',
			[
				'description' => __( 'A product attribute object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'ProductAttribute' ],
				'fields'      => [
					'id'    => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Attribute Global ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $attribute ) {
							return ! empty( $attribute->_relay_id ) ? $attribute->_relay_id : Relay::toGlobalId( 'GlobalProductAttribute', $attribute->get_id() );
						},
					],
					'scope' => [
						'type'        => [ 'non_null' => 'ProductAttributeTypesEnum' ],
						'description' => __( 'Product attribute scope.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function () {
							return 'global';
						},
					],
					'label' => [
						'type'        => 'String',
						'description' => __( 'Attribute label', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $attribute ) {
							$taxonomy = get_taxonomy( $attribute->get_name() );
							return $taxonomy ? ucwords( $taxonomy->labels->singular_name ) : null;
						},
					],
					'name'  => [
						'type'        => 'String',
						'description' => __( 'Product attribute name', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $attribute ) {
							return $attribute->get_name();
						},
					],
					'slug'  => [
						'type'        => 'String',
						'description' => __( 'Product attribute slug', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $attribute ) {
							return ! empty( $attribute->get_name() ) ? $attribute->get_name() : null;
						},
					],
				],
			]
		);

		// ProductAttributeOutput for CartItemError and CartItem edges.
		register_graphql_object_type(
			'ProductAttributeOutput',
			[
				'description' => __( 'A simple product attribute object', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'attributeName'  => [
						'type'        => 'String',
						'description' => __( 'Attribute name.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( array $attribute ) {
							return ! empty( $attribute['attributeName'] ) ? $attribute['attributeName'] : null;
						},
					],
					'attributeValue' => [
						'type'        => 'String',
						'description' => __( 'Attribute value.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( array $attribute ) {
							return ! empty( $attribute['attributeValue'] ) ? $attribute['attributeValue'] : null;
						},
					],
				],
			]
		);
	}
}
