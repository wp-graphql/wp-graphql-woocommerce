<?php
/**
 * Mutation - updateWCSetting
 *
 * Registers mutation for updating a single WooCommerce setting.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Mutation\Settings_Mutation;

/**
 * Class Setting_Update
 */
class Setting_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateWCSetting',
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
			'group' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'Settings group ID.', 'wp-graphql-woocommerce' );
				},
			],
			'id'    => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'Setting ID.', 'wp-graphql-woocommerce' );
				},
			],
			'value' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'Setting value.', 'wp-graphql-woocommerce' );
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
			'setting' => [
				'type'        => 'WCSetting',
				'description' => static function () {
					return __( 'The updated setting.', 'wp-graphql-woocommerce' );
				},
				'resolve'     => static function ( $payload ) {
					return $payload['setting'];
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
			$setting_id = $input['id'];
			$value      = $input['value'];

			$controller = new \WC_REST_Setting_Options_Controller();

			/** @var array|\WP_Error $setting */
			$setting = $controller->get_setting( $group_id, $setting_id );

			if ( is_wp_error( $setting ) ) {
				throw new UserError( $setting->get_error_message() );
			}

			$validated = self::validate_value( $value, $setting );
			self::save_setting( $setting, $validated );

			/** @var array|\WP_Error $updated */
			$updated = $controller->get_setting( $group_id, $setting_id );
			if ( is_wp_error( $updated ) ) {
				throw new UserError( $updated->get_error_message() );
			}

			return [ 'setting' => $updated ];
		};
	}

	/**
	 * Validate a setting value based on its type.
	 *
	 * @param mixed $value   Value to validate.
	 * @param array $setting Setting definition.
	 *
	 * @throws \GraphQL\Error\UserError If validation fails.
	 *
	 * @return mixed
	 */
	public static function validate_value( $value, $setting ) {
		$type   = $setting['type'];
		$method = 'validate_setting_' . $type . '_field';

		if ( method_exists( Settings_Mutation::class, $method ) ) {
			return Settings_Mutation::$method( $value, $setting );
		}

		return Settings_Mutation::validate_setting_text_field( $value, $setting );
	}

	/**
	 * Save a validated setting value.
	 *
	 * @param array $setting Setting definition.
	 * @param mixed $value   Validated value.
	 *
	 * @return void
	 */
	public static function save_setting( $setting, $value ) {
		$option_key = $setting['option_key'];

		if ( is_array( $option_key ) ) {
			$option = get_option( $option_key[0], [] );
			if ( ! is_array( $option ) ) {
				$option = [];
			}
			$option[ $option_key[1] ] = $value;
			update_option( $option_key[0], $option );
		} else {
			\WC_Admin_Settings::save_fields(
				[ $setting ],
				[ $setting['id'] => $value ]
			);
		}
	}
}
