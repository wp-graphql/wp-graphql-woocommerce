<?php
/**
 * WPObject Type - Country_State_Type
 *
 * Registers CountryState WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.12.4
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Country_State_Type
 */
class Country_State_Type {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'CountryState',
			[
				'description' => __( 'shipping country state object', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'code' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Country state code', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['code'] ) ? $source['code'] : null;
						},
					],
					'name' => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Country state name', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['name'] ) ? $source['name'] : null;
						},
					],
				],
			]
		);
	}
}
