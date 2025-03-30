<?php
/**
 * WPObject Type - WC_Setting_Type
 *
 * Registers WCSetting WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class WC_Setting_Type
 */
class WC_Setting_Type {
	/**
	 * Registers WC setting type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'WCSetting',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A WC setting object', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The globally unique identifier for the WC setting.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['id'] ) ? $source['id'] : null;
						},
					],
					'label'       => [
						'type'        => 'String',
						'description' => __( 'A human readable label for the setting used in user interfaces.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['title'] ) ? $source['title'] : null;
						},
					],
					'description' => [
						'type'        => 'String',
						'description' => __( 'A human readable description for the setting used in user interfaces.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['description'] ) ? $source['description'] : null;
						},
					],
					'type'        => [
						'type'        => 'WCSettingTypeEnum',
						'description' => __( 'Type of setting.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['type'] ) ? $source['type'] : null;
						},
					],
					'value'       => [
						'type'        => 'String',
						'description' => __( 'Setting value.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['value'] ) ? $source['value'] : null;
						},
					],
					'default'     => [
						'type'        => 'String',
						'description' => __( 'Default value for the setting.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['default'] ) ? $source['default'] : null;
						},
					],
					'tip'         => [
						'type'        => 'String',
						'description' => __( 'Additional help text shown to the user about the setting', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['desc_tip'] ) ? $source['desc_tip'] : null;
						},
					],
					'placeholder' => [
						'type'        => 'String',
						'description' => __( 'Placeholder text to be displayed in text inputs.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args, $context, $info ) {
							return ! empty( $source['placeholder'] ) ? $source['placeholder'] : null;
						},
					],
				],
			]
		);
	}
}
