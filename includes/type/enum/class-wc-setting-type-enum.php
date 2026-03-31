<?php
/**
 * WPEnum Type - WCSettingTypeEnum
 *
 * Dynamically registers WC setting types as a GraphQL enum,
 * collected from all registered WC settings groups.
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.20.0
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

use WPGraphQL\WooCommerce\Utils\Label;

/**
 * Class WC_Setting_Type_Enum
 */
class WC_Setting_Type_Enum {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$types  = self::collect_types();
		$values = [];

		foreach ( $types as $type ) {
			$enum_name = Label::get_safe_enum_name( strtoupper( $type ) );
			if ( empty( $enum_name ) ) {
				continue;
			}

			$values[ $enum_name ] = [ 'value' => $type ];
		}

		register_graphql_enum_type(
			'WCSettingTypeEnum',
			[
				'description' => static function () {
					return __( 'Type of WC setting.', 'wp-graphql-woocommerce' );
				},
				'values'      => $values,
			]
		);
	}

	/**
	 * Collects all unique setting types from registered WC settings groups.
	 *
	 * @return array
	 */
	private static function collect_types() {
		$groups = apply_filters( 'woocommerce_settings_groups', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$types  = [];

		foreach ( $groups as $group ) {
			$group_id = $group['id'] ?? '';
			if ( empty( $group_id ) ) {
				continue;
			}

			$settings = apply_filters( 'woocommerce_settings-' . $group_id, [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			foreach ( $settings as $setting ) {
				$type = $setting['type'] ?? '';
				if ( ! empty( $type ) ) {
					$types[ $type ] = $type;
				}
			}
		}

		/**
		 * Filters the list of WC setting types registered in the WCSettingTypeEnum.
		 *
		 * Allows third-party plugins to add custom setting types to the enum.
		 *
		 * @param array $types Array of setting type slugs keyed by slug.
		 */
		return apply_filters( 'graphql_woocommerce_setting_types', $types );
	}
}
