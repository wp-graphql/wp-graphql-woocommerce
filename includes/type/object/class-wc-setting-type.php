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
				'description'     => static function () {
					return __( 'A relative date value with a number and unit.', 'graphql-for-ecommerce' );
				},
				'fields'          => [
					'number' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'The number of periods.', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							$number = $source['number'] ?? '';
							return '' !== $number ? absint( $number ) : null;
						},
					],
					'unit'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'The period unit (days, weeks, months, years).', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'WCImageWidth',
			[
				'eagerlyLoadType' => true,
				'description'     => static function () {
					return __( 'An image width value with dimensions and crop flag.', 'graphql-for-ecommerce' );
				},
				'fields'          => [
					'width'  => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Image width in pixels.', 'graphql-for-ecommerce' );
						},
					],
					'height' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'Image height in pixels.', 'graphql-for-ecommerce' );
						},
					],
					'crop'   => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Whether to crop the image.', 'graphql-for-ecommerce' );
						},
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
				'description'     => static function () {
					return __( 'A WC setting with a string value.', 'graphql-for-ecommerce' );
				},
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Setting value.', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							$value = $source['value'] ?? null;
							return is_scalar( $value ) ? (string) $value : null;
						},
					],
					'default' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Default value for the setting.', 'graphql-for-ecommerce' );
						},
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
				'description'     => static function () {
					return __( 'A WC setting with an array value.', 'graphql-for-ecommerce' );
				},
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Setting value as a list of strings.', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source ) {
							$value = $source['value'] ?? null;
							return is_array( $value ) ? array_values( $value ) : null;
						},
					],
					'default' => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Default value as a list of strings.', 'graphql-for-ecommerce' );
						},
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
				'description'     => static function () {
					return __( 'A WC setting with a relative date value.', 'graphql-for-ecommerce' );
				},
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => 'WCRelativeDate',
						'description' => static function () {
							return __( 'Setting value as a relative date.', 'graphql-for-ecommerce' );
						},
					],
					'default' => [
						'type'        => 'WCRelativeDate',
						'description' => static function () {
							return __( 'Default value as a relative date.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);

		register_graphql_object_type(
			'WCImageWidthSetting',
			[
				'eagerlyLoadType' => true,
				'description'     => static function () {
					return __( 'A WC setting with an image width value.', 'graphql-for-ecommerce' );
				},
				'interfaces'      => [ 'WCSetting' ],
				'fields'          => [
					'value'   => [
						'type'        => 'WCImageWidth',
						'description' => static function () {
							return __( 'Setting value as image dimensions.', 'graphql-for-ecommerce' );
						},
					],
					'default' => [
						'type'        => 'WCImageWidth',
						'description' => static function () {
							return __( 'Default value as image dimensions.', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
