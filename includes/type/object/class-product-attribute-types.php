<?php
/**
 * WPObject Types - LocalProductAttribute && GlobalProductAttribute
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Product_Attribute_Types
 */
class Product_Attribute_Types {

	/**
	 * Registers ProductAttribute types
	 */
	public static function register() {
		// Local.
		register_graphql_object_type(
			'LocalProductAttribute',
			array(
				'description' => __( 'A product attribute object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'ProductAttribute' ),
				'fields'      => array(
					'scope'       => array(
						'type'        => array( 'non_null' => 'ProductAttributeTypesEnum' ),
						'description' => __( 'Product attribute scope.', 'wp-graphql-woocommerce' ),
						'resolve'     => function () {
							return 'local';
						},
					),
				),
			)
		);

		// Global.
		register_graphql_object_type(
			'GlobalProductAttribute',
			array(
				'description' => __( 'A product attribute object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'ProductAttribute' ),
				'fields'      => array(
					'scope'       => array(
						'type'        => array( 'non_null' => 'ProductAttributeTypesEnum' ),
						'description' => __( 'Product attribute scope.', 'wp-graphql-woocommerce' ),
						'resolve'     => function () {
							return 'global';
						},
					),
				),
			)
		);
	}
}
