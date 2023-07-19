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

use WPGraphQL\WooCommerce\Type\WPInterface\Payment_Token;

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
			'PaymentTokenCC',
			[
				'description' => __( 'A credit cart payment token', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'PaymentToken' ],
				'fields'      => Payment_Token::get_fields( self::get_credit_card_fields() ),
			]
		);

		register_graphql_object_type(
			'PaymentTokenECheck',
			[
				'description' => __( 'A electronic check payment token', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'PaymentToken' ],
				'fields'      => Payment_Token::get_fields( self::get_e_check_fields() ),
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
				'description' => __( 'Last 4 digits of the stored account number', 'wp-graphql-woocommerce' ),
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
				'description' => __( 'Card type (visa, mastercard, etc)', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_card_type() ) ? $source->get_card_type() : null;
				},
			],
			'expiryYear'  => [
				'type'        => 'String',
				'description' => __( 'Card\'s expiration year.', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_expiry_year() ) ? $source->get_expiry_year() : null;
				},
			],
			'expiryMonth' => [
				'type'        => 'String',
				'description' => __( 'Card\'s expiration month', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_expiry_month() ) ? $source->get_expiry_month() : null;
				},
			],
			'last4'       => [
				'type'        => 'Integer',
				'description' => __( 'Last 4 digits of the stored credit card number', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return ! empty( $source->get_last4() ) ? $source->get_last4() : null;
				},
			],
		];
	}
}
