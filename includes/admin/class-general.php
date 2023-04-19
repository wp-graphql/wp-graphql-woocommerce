<?php
/**
 * Defines WooGraphQL's general settings.
 *
 * @package WPGraphQL\WooCommerce\Admin
 */

namespace WPGraphQL\WooCommerce\Admin;

/**
 * General class
 */
class General extends Section {

	/**
	 * Returns General settings fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			[
				'name'    => 'disable_ql_session_handler',
				'label'   => __( 'Disable QL Session Handler', 'wp-graphql-woocommerce' ),
				'desc'    => __( 'The QL Session Handler takes over management of WooCommerce Session Management on WPGraphQL request replacing the usage of HTTP Cookies with JSON Web Tokens.', 'wp-graphql-woocommerce' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'    => 'enable_unsupported_product_type',
				'label'   => __( 'Enable Unsupported types', 'wp-graphql-woocommerce' ),
				'desc'    => __( 'Substitute unsupported product types with SimpleProduct', 'wp-graphql-woocommerce' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
		];
	}
}
