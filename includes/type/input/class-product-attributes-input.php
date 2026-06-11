<?php
/**
 * WPInputObjectType - ProductAttributesInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Attributes_Input
 */
class Product_Attributes_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductAttributesInput',
			[
				'description' => static function () {
					return __( 'Product attribute properties', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'id'        => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Attribute ID', 'graphql-for-ecommerce' );
						},
					],
					'name'      => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Attribute name', 'graphql-for-ecommerce' );
						},
					],
					'position'  => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Attribute position', 'graphql-for-ecommerce' );
						},
					],
					'visible'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Define if the attribute is visible on the "Additional information" tab in the product\'s page. Default is false.', 'graphql-for-ecommerce' );
						},
					],
					'variation' => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Define if the attribute can be used as variation. Default is false.', 'graphql-for-ecommerce' );
						},
					],
					'options'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'List of available term names for the attribute', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
