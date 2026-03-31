<?php
/**
 * Mutation - updateWCSettings
 *
 * Registers mutation for batch updating WooCommerce settings within a group.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;

/**
 * Class Settings_Update
 */
class Settings_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateWCSettings',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'group'    => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'Settings group ID.', 'wp-graphql-woocommerce' );
				},
			],
			'settings' => [
				'type'        => [ 'non_null' => [ 'list_of' => 'WCSettingInput' ] ],
				'description' => static function () {
					return __( 'Settings to update.', 'wp-graphql-woocommerce' );
				},
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'settings' => [
				'type'        => [ 'list_of' => 'WCSetting' ],
				'description' => static function () {
					return __( 'The updated settings.', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $payload ) {
					return $payload['settings'];
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input ) {
			if ( ! \wc_rest_check_manager_permissions( 'settings', 'edit' ) ) {
				throw new UserError( __( 'Sorry, you cannot update settings.', 'wp-graphql-woocommerce' ) );
			}

			$group_id   = $input['group'];
			$controller = new \WC_REST_Setting_Options_Controller();
			$updated    = [];

			foreach ( $input['settings'] as $setting_input ) {
				$setting_id = $setting_input['id'];
				$value      = $setting_input['value'];

				/** @var array|\WP_Error $setting */
				$setting = $controller->get_setting( $group_id, $setting_id );
				if ( is_wp_error( $setting ) ) {
					throw new UserError( $setting->get_error_message() );
				}

				$validated = Setting_Update::validate_value( $value, $setting );
				Setting_Update::save_setting( $setting, $validated );

				/** @var array|\WP_Error $refreshed */
				$refreshed = $controller->get_setting( $group_id, $setting_id );
				if ( is_wp_error( $refreshed ) ) {
					throw new UserError( $refreshed->get_error_message() );
				}

				$updated[] = $refreshed;
			}

			return [ 'settings' => $updated ];
		};
	}
}
