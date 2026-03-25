<?php
/**
 * Label utility functions for GraphQL name formatting.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Utils;

use WPGraphQL\Type\WPEnumType;

/**
 * Class Label
 */
class Label {
	/**
	 * Returns a safe GraphQL enum name for the given value, optionally
	 * transliterating non-latin characters when the setting is enabled.
	 *
	 * Falls back to WPEnumType::get_safe_name() and returns null when
	 * the result contains no alphanumeric characters (i.e. is meaningless).
	 *
	 * @param string $value The raw value to convert to a safe enum name.
	 *
	 * @return string|null The safe name, or null if the value cannot produce a valid GraphQL name.
	 */
	public static function get_safe_enum_name( string $value ): ?string {
		if (
			'on' === woographql_setting( 'enable_transliteration', 'off' )
			&& function_exists( 'transliterator_transliterate' )
			&& preg_match( '/[^\x20-\x7E]/', $value )
		) {
			$value = transliterator_transliterate( 'Any-Latin; Latin-ASCII', $value );
		}

		if ( empty( $value ) ) {
			return null;
		}

		$safe_name = WPEnumType::get_safe_name( $value );

		if ( ! preg_match( '/[A-Za-z0-9]/', $safe_name ) ) {
			return null;
		}

		return $safe_name;
	}
}
