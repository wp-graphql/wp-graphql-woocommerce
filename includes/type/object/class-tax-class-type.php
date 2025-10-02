<?php
/**
 * WPObject Type - Tax_Class_Type
 *
 * Registers TaxClass WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Tax_Class_Type
 */
class Tax_Class_Type {
	/**
	 * Registers tax class type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'TaxClass',
			array(
				'eagerlyLoadType' => true,
				'description'     => __( 'A Tax class object', 'wp-graphql-woocommerce' ),
				'interfaces'      => array( 'Node' ),
				'fields'          => array(
					'id'   => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the tax class.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['slug'] ) ? \GraphQLRelay\Relay::toGlobalId( 'tax_class', $source['slug'] ) : null;
						},
					),
					'slug' => array(
						'type'        => 'String',
						'description' => __( 'The globally unique identifier for the tax class.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['slug'] ) ? $source['slug'] : null;
						},
					),
					'name' => array(
						'type'        => 'String',
						'description' => __( 'Tax class name.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['name'] ) ? $source['name'] : null;
						},
					),
				),
			)
		);
	}
}
