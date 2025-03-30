<?php
/**
 * Defines helper functions for executing mutations related to the WC Settings API.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;

/**
 * Class - Settings_Mutation
 */
class Settings_Mutation {
	/**
	 * Validate a text value for a text based setting.
	 *
	 * @param string $value Value.
	 * @param array  $setting Setting.
	 * @return string
	 */
	public static function validate_setting_text_field( $value, $setting ) {
		$value = is_null( $value ) ? '' : $value;
		return wp_kses_post( trim( stripslashes( $value ) ) );
	}

	/**
	 * Validate select based settings.
	 *
	 * @param string $value Value.
	 * @param array  $setting Setting.
	 *
	 * @throws \GraphQL\Error\UserError If an invalid setting value was passed.
	 *
	 * @return string
	 */
	public static function validate_setting_select_field( $value, $setting ) {
		if ( array_key_exists( $value, $setting['options'] ) ) {
			return $value;
		} else {
			throw new UserError( __( 'An invalid setting value was passed.', 'wp-graphql-woocommerce' ), 400 );
		}
	}

	/**
	 * Validate multiselect based settings.
	 *
	 * @param array $values Values.
	 * @param array $setting Setting.
	 *
	 * @throws \GraphQL\Error\UserError If an invalid setting value was passed.
	 *
	 * @return array
	 */
	public static function validate_setting_multiselect_field( $values, $setting ) {
		if ( empty( $values ) ) {
			return [];
		}

		if ( ! is_array( $values ) ) {
			throw new UserError( __( 'An invalid setting value was passed.', 'wp-graphql-woocommerce' ), 400 );
		}

		$final_values = [];
		foreach ( $values as $value ) {
			if ( array_key_exists( $value, $setting['options'] ) ) {
				$final_values[] = $value;
			}
		}

		return $final_values;
	}

	/**
	 * Validate image_width based settings.
	 *
	 * @param array $values Values.
	 * @param array $setting Setting.
	 *
	 * @throws \GraphQL\Error\UserError If an invalid setting value was passed.
	 *
	 * @return string
	 */
	public static function validate_setting_image_width_field( $values, $setting ) {
		if ( ! is_array( $values ) ) {
			throw new UserError( __( 'An invalid setting value was passed.', 'wp-graphql-woocommerce' ), 400 );
		}

		$current = $setting['value'];
		if ( isset( $values['width'] ) ) {
			$current['width'] = intval( $values['width'] );
		}
		if ( isset( $values['height'] ) ) {
			$current['height'] = intval( $values['height'] );
		}
		if ( isset( $values['crop'] ) ) {
			$current['crop'] = (bool) $values['crop'];
		}
		return $current;
	}

	/**
	 * Validate radio based settings.
	 *
	 * @param string $value Value.
	 * @param array  $setting Setting.
	 *
	 * @throws \GraphQL\Error\UserError If an invalid setting value was passed.
	 *
	 * @return string
	 */
	public static function validate_setting_radio_field( $value, $setting ) {
		return self::validate_setting_select_field( $value, $setting );
	}

	/**
	 * Validate checkbox based settings.
	 *
	 * @param string $value Value.
	 * @param array  $setting Setting.
	 *
	 * @throws \GraphQL\Error\UserError If an invalid setting value was passed.
	 *
	 * @return string
	 */
	public function validate_setting_checkbox_field( $value, $setting ) {
		if ( in_array( $value, [ 'yes', 'no' ], true ) ) {
			return $value;
		} elseif ( empty( $value ) ) {
			$value = isset( $setting['default'] ) ? $setting['default'] : 'no';
			return $value;
		} else {
			throw new UserError( __( 'An invalid setting value was passed.', 'wp-graphql-woocommerce' ), 400 );
		}
	}

	/**
	 * Validate textarea based settings.
	 *
	 * @param string $value Value.
	 * @param array  $setting Setting.
	 *
	 * @return string
	 */
	public static function validate_setting_textarea_field( $value, $setting ) {
		$value = is_null( $value ) ? '' : $value;
		return wp_kses(
			trim( stripslashes( $value ) ),
			array_merge(
				[
					'iframe' => [
						'src'   => true,
						'style' => true,
						'id'    => true,
						'class' => true,
					],
				],
				wp_kses_allowed_html( 'post' )
			)
		);
	}
}
