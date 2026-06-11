<?php
/**
 * WPObject Type - Payment_Token_Types
 *
 * Registers PaymentToken Interface child types.
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.12.4
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WPGraphQL\WooCommerce\Type\WPInterface\Payment_Token_Interface;

/**
 * Class Payment_Token_Types
 */
class Payment_Token_Types {
	/**
	 * Registers types
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'PaymentToken',
			[
				'description' => static function () {
					return __( 'A payment token', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'PaymentTokenInterface' ],
				'fields'      => [],
			]
		);
		register_graphql_object_type(
			'PaymentTokenCC',
			[
				'description' => static function () {
					return __( 'A credit card payment token', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'PaymentTokenInterface' ],
				'fields'      => Payment_Token_Interface::get_fields( self::get_credit_card_fields() ),
			]
		);

		register_graphql_object_type(
			'PaymentTokenECheck',
			[
				'description' => static function () {
					return __( 'An electronic check payment token', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'PaymentTokenInterface' ],
				'fields'      => Payment_Token_Interface::get_fields( self::get_e_check_fields() ),
			]
		);
	}

	/**
	 * Returns field definitions for PaymentTokenECheck  type.
	 *
	 * @return array
	 */
	public static function get_e_check_fields() {
		return [
			'last4' => [
				'type'        => 'Integer',
				'description' => static function () {
					return __( 'Last 4 digits of the stored account number', 'graphql-for-ecommerce' );
				},
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_last4() ) ? $source->get_last4() : null;
				},
			],
		];
	}

	/**
	 * Returns field definitions for PaymentTokenCC type.
	 *
	 * @return array
	 */
	public static function get_credit_card_fields() {
		return [
			'cardType'    => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Card type (visa, mastercard, etc)', 'graphql-for-ecommerce' );
				},
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_card_type() ) ? $source->get_card_type() : null;
				},
			],
			'expiryYear'  => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Card\'s expiration year.', 'graphql-for-ecommerce' );
				},
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_expiry_year() ) ? $source->get_expiry_year() : null;
				},
			],
			'expiryMonth' => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Card\'s expiration month', 'graphql-for-ecommerce' );
				},
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_expiry_month() ) ? $source->get_expiry_month() : null;
				},
			],
			'last4'       => [
				'type'        => 'Integer',
				'description' => static function () {
					return __( 'Last 4 digits of the stored credit card number', 'graphql-for-ecommerce' );
				},
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_last4() ) ? $source->get_last4() : null;
				},
			],
		];
	}
}
