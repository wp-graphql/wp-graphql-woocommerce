<?php
/**
 * Defines generic functions for to be used in connections that process creates using the Db Loader.
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.5.0
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

/**
 * Trait WC_Db_Loader_Common
 */
trait WC_Db_Loader_Common {
	/**
	 * Given an offset and prefix, a cursor is returned
	 *
	 * @param string         $prefix Salt.
	 * @param string|integer $offset Connection offset.
	 *
	 * @return string
	 */
	public function offset_to_cursor( $prefix, $offset ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( "{$prefix}:{$offset}" );
	}

	/**
	 * Given a valid cursor and prefix, the offset is returned
	 *
	 * @param string $prefix Salt.
	 * @param string $cursor Cursor.
	 *
	 * @return string|integer
	 */
	public function cursor_to_offset( $prefix, $cursor ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		return substr( base64_decode( $cursor ), strlen( $prefix . ':' ) );
	}
}
