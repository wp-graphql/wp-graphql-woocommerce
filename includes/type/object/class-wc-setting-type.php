<?php
/**
 * WPObject Types - WC_Setting_Type
 *
 * Registers WCSetting concrete implementations and helper types.
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
	 * Registers WCSetting concrete types and helper object types.
	 *
	 * @return void
	 */
	public static function register() {
		self::register_helper_types();
		self::register_concrete_types();
	}

	/**
	 * Registers helper object types used by setting value fields.
	 *
	 * @return void
	 */
	private static function register_helper_types() {
		register_graphql_object_type(
			'WCRelativeDate',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A relative date value with a number and unit.', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'number' => [
						'type'        => 'Int',
						'description' => __( 'The number of periods.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$number = $source['number'] ?? '';
							return '' !== $number ? absint( $number ) : null;
						},
					],
					'unit'   => [
						'type'        => 'String',
						'description' => __( 'The period unit (days, weeks, months, years).', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_object_type(
			'WCImageWidth',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'An image width value with dimensions and crop flag.', 'wp-graphql-woocommerce' ),
				'fields'          => [
					'width'  => [
						'type'        => 'Int',
						'description' => __( 'Image width in pixels.', 'wp-graphql-woocommerce' ),
					],
					'height' => [
						'type'        => 'Int',
						'description' => __( 'Image height in pixels.', 'wp-graphql-woocommerce' ),
					],
					'crop'   => [
						'type'        => 'Boolean',
						'description' => __( 'Whether to crop the image.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source['crop'] );
						},
					],
				],
			]
		);
	}

	/**
	 * Registers concrete setting types that implement the WCSetting interface.
	 *
	 * @return void
	 */
	private static function register_concrete_types() {
		register_graphql_object_type(
			'WCStringSetting',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A WC setting with a string value.', 'wp-graphql-woocommerce' ),
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => 'String',
						'description' => __( 'Setting value.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$value = $source['value'] ?? null;
							return is_scalar( $value ) ? (string) $value : null;
						},
					],
					'default' => [
						'type'        => 'String',
						'description' => __( 'Default value for the setting.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$value = $source['default'] ?? null;
							return ! empty( $value ) && is_scalar( $value ) ? (string) $value : null;
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'WCArraySetting',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A WC setting with an array value.', 'wp-graphql-woocommerce' ),
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'Setting value as a list of strings.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$value = $source['value'] ?? null;
							return is_array( $value ) ? array_values( $value ) : null;
						},
					],
					'default' => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'Default value as a list of strings.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$value = $source['default'] ?? null;
							return is_array( $value ) ? array_values( $value ) : null;
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'WCRelativeDateSetting',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A WC setting with a relative date value.', 'wp-graphql-woocommerce' ),
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => 'WCRelativeDate',
						'description' => __( 'Setting value as a relative date.', 'wp-graphql-woocommerce' ),
					],
					'default' => [
						'type'        => 'WCRelativeDate',
						'description' => __( 'Default value as a relative date.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);

		register_graphql_object_type(
			'WCImageWidthSetting',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A WC setting with an image width value.', 'wp-graphql-woocommerce' ),
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => 'WCImageWidth',
						'description' => __( 'Setting value as image dimensions.', 'wp-graphql-woocommerce' ),
					],
					'default' => [
						'type'        => 'WCImageWidth',
						'description' => __( 'Default value as image dimensions.', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
