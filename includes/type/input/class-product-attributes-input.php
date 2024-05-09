<?php
/**
 * WPInputObjectType - ProductAttributesInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   TBD
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
				'description' => __( 'Product attribute properties', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'id'        => [
						'type'        => 'Int',
						'description' => __( 'Attribute ID', 'wp-graphql-woocommerce' ),
					],
					'name'      => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Attribute name', 'wp-graphql-woocommerce' ),
					],
					'position'  => [
						'type'        => 'Int',
						'description' => __( 'Attribute position', 'wp-graphql-woocommerce' ),
					],
					'visible'   => [
						'type'        => 'Boolean',
						'description' => __( 'Define if the attribute is visible on the "Additional information" tab in the product\'s page. Default is false.', 'wp-graphql-woocommerce' ),
					],
					'variation' => [
						'type'        => 'Boolean',
						'description' => __( 'Define if the attribute can be used as variation. Default is false.', 'wp-graphql-woocommerce' ),
					],
					'options'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'List of available term names for the attribute', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
