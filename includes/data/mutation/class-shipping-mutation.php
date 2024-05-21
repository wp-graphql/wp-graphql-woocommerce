<?php
/**
 * Defines helper functions for executing mutations related to shipping.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

/**
 * Class - Shipping_Mutation
 */
class Shipping_Mutation {
	/**
	 * Maps the settings input for insertion.
	 *
	 * @param array<array<string, string>> $settings_input  Settings input.
	 *
	 * @return array<string, string>
	 */
	private static function flatten_settings_input( $settings_input ) {
		$settings = [];
		foreach ( $settings_input as $setting ) {
			$settings[ $setting['id'] ] = $setting['value'];
		}
		return $settings;
	}

	/**
	 * Updates settings on a shipping zone method.
	 *
	 * @param int                 $instance_id     Instance ID.
	 * @param \WC_Shipping_Method $method          Shipping method data.
	 * @param array               $settings_input  Settings input.
	 *
	 * @return \WC_Shipping_Method
	 */
	public static function set_shipping_zone_method_settings( $instance_id, $method, $settings_input ) {
		$settings = self::flatten_settings_input( $settings_input );
		$method->init_instance_settings();
		$instance_settings = $method->instance_settings;
		$errors_found      = false;
		foreach ( $method->get_instance_form_fields() as $key => $field ) {
			if ( isset( $settings[ $key ] ) ) {
				if ( is_callable( [ Settings_Mutation::class, 'validate_setting_' . $field['type'] . '_field' ] ) ) {
					$value = Settings_Mutation::{'validate_setting_' . $field['type'] . '_field'}( $settings[ $key ], $field );
				} else {
					$value = Settings_Mutation::validate_setting_text_field( $settings[ $key ], $field );
				}
				$instance_settings[ $key ] = $value;
			}
		}

		update_option(
			$method->get_instance_option_key(),
			apply_filters(
				'woocommerce_shipping_' . $method->id . '_instance_settings_values', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				$instance_settings,
				$method
			)
		);

		$method->instance_settings = $instance_settings;
		return $method;
	}

	/**
	 * Updates the order of a shipping zone method.
	 *
	 * @param int                 $instance_id  Instance ID.
	 * @param \WC_Shipping_Method $method       Shipping method data.
	 * @param int                 $order        Order.
	 *
	 * @return \WC_Shipping_Method
	 */
	public static function set_shipping_zone_method_order( $instance_id, $method, $order ) {
		global $wpdb;

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"{$wpdb->prefix}woocommerce_shipping_zone_methods",
			[ 'method_order' => $order ],
			[ 'instance_id' => $instance_id ]
		);
		$method->method_order = $order;

		return $method;
	}

	/**
	 * Updates the enabled status of a shipping zone method.
	 *
	 * @param int                 $zone_id      Zone ID.
	 * @param int                 $instance_id  Instance ID.
	 * @param \WC_Shipping_Method $method       Shipping method data.
	 * @param bool                $enabled      Enabled status.
	 *
	 * @return \WC_Shipping_Method
	 */
	public static function set_shipping_zone_method_enabled( $zone_id, $instance_id, $method, $enabled ) {
		global $wpdb;

		if ( $wpdb->update( "{$wpdb->prefix}woocommerce_shipping_zone_methods", [ 'is_enabled' => $enabled ], [ 'instance_id' => $instance_id ] ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			do_action( 'woocommerce_shipping_zone_method_status_toggled', $instance_id, $method->id, $zone_id, $enabled ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$method->enabled = ( true === $enabled ? 'yes' : 'no' );
		}

		return $method;
	}
}
