<?php
/**
 * WPInterface Type - Attribute_Type
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.10.1
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

/**
 * Class Attribute
 */
class Attribute {
	/**
	 * Registers the "Attribute" interface.
	 *
	 * @return void
	 */
	public static function register_interface() {
		register_graphql_interface_type(
			'Attribute',
			[
				'description' => __( 'Attribute object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'name'  => [
						'type'        => 'String',
						'description' => __( 'Name of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source['name'] ) ? $source['name'] : null;
						},
					],
					'value' => [
						'type'        => 'String',
						'description' => __( 'Selected value of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return isset( $source['value'] ) ? $source['value'] : null;
						},
					],
				],
				'resolveType' => static function ( $value ) {
					$type_registry = \WPGraphQL::get_type_registry();
					if ( $value->is_taxonomy() ) {
						return $type_registry->get_type( 'SimpleAttribute' );
					} else {
						return $type_registry->get_type( 'VariationAttribute' );
					}
				},
			]
		);
	}
}
