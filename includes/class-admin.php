<?php
/**
 * Initializes a WooGraphQL Pro admin settings.
 *
 * @package WPGraphQL\WooCommerce\Pro
 * @since 1.0.0
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\Admin\Settings\Settings;
use WPGraphQL\WooCommerce\Admin\General;

/**
 * Class Admin
 */
class Admin {
	/**
	 * Admin constructor
	 */
	public function __construct() {
		add_action( 'graphql_register_settings', [ $this, 'register_settings' ] );
	}

	/**
	 * Registers the WooGraphQL Settings tab.
	 *
	 * @param \WPGraphQL\Admin\Settings\Settings $manager  Settings Manager.
	 * @return void
	 */
	public function register_settings( Settings $manager ) {
		$manager->settings_api->register_section(
			'woographql_settings',
			[ 'title' => __( 'WooGraphQL', 'wp-graphql-woocommerce' ) ]
		);

		$manager->settings_api->register_fields(
			'woographql_settings',
			General::get_fields()
		);
	}
}
