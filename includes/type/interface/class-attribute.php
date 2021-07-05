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
	 * Registers the "Product" interface.
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry  Instance of the WPGraphQL TypeRegistry.
	 */
	public static function register_interface( &$type_registry ) {
		register_graphql_interface_type(
			'Attribute',
			array(
				'description' => __( 'Attribute object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'name'  => array(
						'type'        => 'String',
						'description' => __( 'Name of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['name'] ) ? $source['name'] : null;
						},
					),
					'value' => array(
						'type'        => 'String',
						'description' => __( 'Selected value of attribute', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['value'] ) ? $source['value'] : null;
						},
					),
				),
				'resolveType' => function( $value ) use ( &$type_registry ) {
					if ( $value->is_taxonomy() ) {
						return $type_registry->get_type( 'SimpleAttribute' );
					} else {
						return $type_registry->get_type( 'VariationAttribute' );
					}
				},
			)
		);
	}
}
