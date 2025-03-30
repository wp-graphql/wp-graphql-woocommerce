<?php
/**
 * The section defines the root functionality for a settings section
 *
 * @package WPGraphQL\WooCommerce\Admin
 */

namespace WPGraphQL\WooCommerce\Admin;

/**
 * Section class
 */
abstract class Section {
	/**
	 * Returns Section settings fields.
	 *
	 * @return array
	 */
	abstract public static function get_fields();
}
