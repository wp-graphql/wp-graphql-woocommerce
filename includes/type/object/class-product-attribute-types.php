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
				'description' => static function () {
					return __( 'A product attribute object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'ProductAttribute' ],
				'fields'      => [
					'id'    => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'Attribute Global ID', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $attribute ) {
							$id_parts = [ $attribute->get_name() ];
							if ( ! empty( $attribute->_product_id ) ) {
								$id_parts[] = $attribute->_product_id;
							}
							return Relay::toGlobalId( 'LocalProductAttribute', implode( ':', $id_parts ) );
						},
					],
					'scope' => [
						'type'        => [ 'non_null' => 'ProductAttributeTypesEnum' ],
						'description' => static function () {
							return __( 'Product attribute scope.', 'graphql-for-ecommerce' );
						},
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
				'description' => static function () {
					return __( 'A product attribute object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'ProductAttribute' ],
				'fields'      => [
					'id'    => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'Attribute Global ID', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $attribute ) {
							return Relay::toGlobalId( 'GlobalProductAttribute', $attribute->get_id() );
						},
					],
					'scope' => [
						'type'        => [ 'non_null' => 'ProductAttributeTypesEnum' ],
						'description' => static function () {
							return __( 'Product attribute scope.', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function () {
							return 'global';
						},
					],
					'label' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Attribute label', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $attribute ) {
							$taxonomy = get_taxonomy( $attribute->get_name() );
							return $taxonomy ? $taxonomy->labels->singular_name : null;
						},
					],
					'name'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Product attribute name', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $attribute ) {
							return $attribute->get_name();
						},
					],
					'slug'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Product attribute slug', 'graphql-for-ecommerce' );
						},
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
				'description' => static function () {
					return __( 'A simple product attribute object', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'attributeName'  => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Attribute name.', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( array $attribute ) {
							return ! empty( $attribute['attributeName'] ) ? $attribute['attributeName'] : null;
						},
					],
					'attributeValue' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Attribute value.', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( array $attribute ) {
							return ! empty( $attribute['attributeValue'] ) ? $attribute['attributeValue'] : null;
						},
					],
				],
			]
		);
	}
}
