<?php
/**
 * Factory class for the WooCommerce's Cart data objects.
 *
 * @since v0.8.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Cart factory class for testing.
 */
class PaymentTokenFactory {

	/**
	 * Create a new credit card payment token
	 *
	 * @since 2.6
	 * @return \WC_Payment_Token_CC object
	 */
	public static function createCCToken( $user_id = '', $args = [] ) {
		$token = new \WC_Payment_Token_CC();
		$token->set_last4( 1234 );
		$token->set_expiry_month( '08' );
		$token->set_expiry_year( '2016' );
		$token->set_card_type( 'visa' );
		$token->set_token( time() );
		if ( ! empty( $user_id ) ) {
			$token->set_user_id( $user_id );
		}

		// Set props.
		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $token, "set_{$key}" ] ) ) {
				$token->{"set_{$key}"}( $value );
			}
		}

		$token->save();
		return $token;
	}

	/**
	 * Create a new eCheck payment token
	 *
	 * @since 2.6
	 * @return \WC_Payment_Token_ECheck object
	 */
	public static function createECheckToken( $user_id = '', $args = [] ) {
		$token = new \WC_Payment_Token_ECheck();
		$token->set_last4( 1234 );
		$token->set_token( time() );

		if ( ! empty( $user_id ) ) {
			$token->set_user_id( $user_id );
		}

		// Set props.
		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $token, "set_{$key}" ] ) ) {
				$token->{"set_{$key}"}( $value );
			}
		}

		$token->save();
		return $token;
	}

	/**
	 * Create a new 'stub' payment token
	 *
	 * @since 2.6
	 * @param  string $extra A string to insert and get to test the metadata functionality of a token
	 * @return \WC_Payment_Token_Stub object
	 */
	public static function createStubToken( $extra, $args = [] ) {
		$token = new \WC_Payment_Token_Stub();
		$token->set_extra( $extra );
		$token->set_token( time() );

		// Set props.
		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $token, "set_{$key}" ] ) ) {
				$token->{"set_{$key}"}( $value );
			}
		}

		$token->save();
		return $token;
	}
}
