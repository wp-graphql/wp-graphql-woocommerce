<?php
/**
 * WPInterface Type - WCSetting
 *
 * Registers WCSetting interface with common fields shared
 * by all WC setting concrete types.
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

/**
 * Class WC_Setting
 */
class WC_Setting {
	/**
	 * Mapping of WC setting types to normalized enum values.
	 *
	 * @var array
	 */
	private static $type_map = [
		'multi_select_countries'         => 'multiselect',
		'single_select_country'          => 'select',
		'single_select_page'             => 'select',
		'single_select_page_with_search' => 'select',
		'thumbnail_cropping'             => 'text',
	];

	/**
	 * Default setting type to GraphQL concrete type mapping.
	 *
	 * @var array
	 */
	private static $default_graphql_type_map = [
		'multiselect'            => 'WCArraySetting',
		'relative_date_selector' => 'WCRelativeDateSetting',
		'image_width'            => 'WCImageWidthSetting',
	];

	/**
	 * Registers the WCSetting interface.
	 *
	 * @return void
	 */
	public static function register_interface() {
		register_graphql_interface_type(
			'WCSetting',
			[
				'eagerlyLoadType' => true,
				'description'     => static function () {
					return __( 'A WC setting object', 'wp-graphql-woocommerce' );
				},
				'resolveType'     => [ self::class, 'resolve_type' ],
				'fields'          => [
					'id'          => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The globally unique identifier for the WC setting.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							return $source['id'] ?? null;
						},
					],
					'label'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A human readable label for the setting used in user interfaces.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							return $source['label'] ?? $source['title'] ?? null;
						},
					],
					'groupId'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'The ID of the settings group this setting belongs to.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							return $source['group_id'] ?? null;
						},
					],
					'description' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A human readable description for the setting used in user interfaces.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['description'] ) ? $source['description'] : null;
						},
					],
					'type'        => [
						'type'        => 'WCSettingTypeEnum',
						'description' => static function () {
							return __( 'Type of setting.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							$raw_type = $source['type'] ?? '';
							return self::$type_map[ $raw_type ] ?? ( ! empty( $raw_type ) ? $raw_type : null );
						},
					],
					'tip'         => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Additional help text shown to the user about the setting', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['desc_tip'] ) ? $source['desc_tip'] : ( ! empty( $source['tip'] ) ? $source['tip'] : null );
						},
					],
					'placeholder' => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Placeholder text to be displayed in text inputs.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['placeholder'] ) ? $source['placeholder'] : null;
						},
					],
					'options'     => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'Array of option key/value pairs for select and multiselect types.', 'wp-graphql-woocommerce' );
						},
						'resolve'     => static function ( $source ) {
							if ( empty( $source['options'] ) || ! is_array( $source['options'] ) ) {
								return null;
							}

							$options = [];
							foreach ( $source['options'] as $key => $label ) {
								$options[] = $key . ':' . $label;
							}

							return $options;
						},
					],
				],
			]
		);
	}

	/**
	 * Resolves a setting array to the correct concrete GraphQL type.
	 *
	 * @param array $source The setting data array.
	 *
	 * @return string The GraphQL type name.
	 */
	public static function resolve_type( $source ) {
		$raw_type        = $source['type'] ?? '';
		$normalized_type = self::$type_map[ $raw_type ] ?? $raw_type;
		if ( empty( $normalized_type ) ) {
			$normalized_type = 'text';
		}

		/**
		 * Filters the mapping of WC setting types to GraphQL concrete type names.
		 *
		 * Allows third-party plugins to register custom setting types and map them
		 * to their own GraphQL types that implement the WCSetting interface.
		 *
		 * @param array  $type_map        Map of WC setting type => GraphQL type name.
		 * @param string $normalized_type  The normalized setting type.
		 * @param array  $source           The raw setting data.
		 */
		$graphql_type_map = apply_filters(
			'graphql_woocommerce_setting_type_map',
			self::$default_graphql_type_map,
			$normalized_type,
			$source
		);

		return $graphql_type_map[ $normalized_type ] ?? 'WCStringSetting';
	}
}
