<?php
/**
 * WPInterface Type - Payment_Token_Interface
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.10.1
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQLRelay\Relay;

/**
 * Class Payment_Token_Interface
 */
class Payment_Token_Interface {
	/**
	 * Registers the "PaymentToken" interface.
	 *
	 * @return void
	 */
	public static function register_interface() {
		register_graphql_interface_type(
			'PaymentTokenInterface',
			[
				'description' => static function () {
					return __( 'Payment token object', 'wp-graphql-woocommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => static function ( $value ) {
					$type_registry = \WPGraphQL::get_type_registry();
					$type          = $value->get_type();
					switch ( $type ) {
						case 'CC':
							return $type_registry->get_type( 'PaymentTokenCC' );
						case 'eCheck':
							return $type_registry->get_type( 'PaymentTokenECheck' );
						default:
							return $type_registry->get_type( 'PaymentToken' );
					}
				},
			]
		);
	}

	/**
	 * Return field definitions.
	 *
	 * @param array $other_fields  Optional fields to be added.
	 * @return array
	 */
	public static function get_fields( $other_fields = [] ) {
		return array_merge(
			[
				'id'        => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => static function () {
					return __( 'Token ID unique identifier', 'wp-graphql-woocommerce' );
				},
					'resolve'     => static function ( $source ) {
						return ! empty( $source->get_id() ) ? Relay::toGlobalId( 'token', $source->get_id() ) : null;
					},
				],
				'tokenId'   => [
					'type'        => [ 'non_null' => 'Integer' ],
					'description' => static function () {
					return __( 'Token database ID.', 'wp-graphql-woocommerce' );
				},
					'resolve'     => static function ( $source ) {
						return ! empty( $source->get_id() ) ? $source->get_id() : null;
					},
				],
				'type'      => [
					'type'        => [ 'non_null' => 'String' ],
					'description' => static function () {
					return __( 'Token type', 'wp-graphql-woocommerce' );
				},
					'resolve'     => static function ( $source ) {
						return ! empty( $source->get_type() ) ? $source->get_type() : null;
					},
				],
				'gateway'   => [
					'type'        => 'PaymentGateway',
					'description' => static function () {
					return __( 'Token payment gateway', 'wp-graphql-woocommerce' );
				},
					'resolve'     => static function ( $source ) {
						$gateways   = \WC()->payment_gateways()->payment_gateways();
						$gateway_id = $source->get_gateway_id();
						if ( isset( $gateways[ $gateway_id ] ) ) {
							return $gateways[ $gateway_id ];
						}

						return null;
					},
				],
				'isDefault' => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Is token connected to user\'s preferred payment method', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $source ) {
						return ! is_null( $source->is_default() ) ? $source->is_default() : false;
					},
				],
			],
			$other_fields
		);
	}
}
