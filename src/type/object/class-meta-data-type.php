<?php
/**
 * WPObject Type - MetaData
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Data\DataSource;
use GraphQLRelay\Relay;

/**
 * Class Meta_Data_Type
 */
class Meta_Data_Type {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_object_type(
			'MetaData',
			array(
				'description' => __( 'A meta data object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'    => array(
						'type'        => array( 'non_null' => 'Int' ),
						'description' => __( 'Meta ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['id'] ) ? $source['id'] : null;
						},
					),
					'key'   => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta key', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['key'] ) ? $source['key'] : null;
						},
					),
					'value' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta value', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['value'] ) ? $source['value'] : null;
						},
					),
				),
			)
		);
	}
}
