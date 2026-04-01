<?php
/**
 * WPInterface Type - Product_Attribute_Type
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

/**
 * Class Product_Attribute
 */
class Product_Attribute {
	/**
	 * Registers the "Product" interface.
	 *
	 * @return void
	 */
	public static function register_interface() {
		register_graphql_interface_type(
			'ProductAttribute',
			[
				'description' => static function () {
					return __( 'Product attribute object', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => static function ( $value ) {
					$type_registry = \WPGraphQL::get_type_registry();
					if ( $value->is_taxonomy() ) {
						return $type_registry->get_type( 'GlobalProductAttribute' );
					} else {
						return $type_registry->get_type( 'LocalProductAttribute' );
					}
				},
			]
		);
	}

	/**
	 * Defines ProductAttribute fields. All child type must have these fields as well.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'          => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => static function () {
					return __( 'Attribute Global ID', 'wp-graphql-woocommerce' );
				},
			],
			'attributeId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'Attribute ID', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return ! is_null( $attribute->get_id() ) ? $attribute->get_id() : null;
				},
			],
			'name'        => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Attribute name', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return ! empty( $attribute->get_name() ) ? sanitize_title( $attribute->get_name() ) : null;
				},
			],
			'label'       => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Attribute label', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return ! empty( $attribute->get_name() ) ? $attribute->get_name() : null;
				},
			],
			'options'     => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => static function () {
					return __( 'Attribute options', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					$slugs = $attribute->get_slugs();
					return ! empty( $slugs ) ? $slugs : [];
				},
			],
			'position'    => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Attribute position', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return ! is_null( $attribute->get_position() ) ? $attribute->get_position() : null;
				},
			],
			'visible'     => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is attribute visible', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return ! is_null( $attribute->get_visible() ) ? $attribute->get_visible() : null;
				},
			],
			'variation'   => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is attribute on product variation', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return ! is_null( $attribute->get_variation() ) ? $attribute->get_variation() : null;
				},
			],
			'scope'       => [
				'type'        => [ 'non_null' => 'ProductAttributeTypesEnum' ],
				'description' => static function () {
					return __( 'Product attribute scope.', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $attribute ) {
					return $attribute->is_taxonomy() ? 'global' : 'local';
				},
			],
		];
	}
}
