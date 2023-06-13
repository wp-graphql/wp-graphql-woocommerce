<?php
/**
 * WPInterface Type - Payment_Token
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.10.1
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQLRelay\Relay;
use GraphQL\Error\UserError;

/**
 * Class Payment_Token
 */
class Payment_Token {

	/**
	 * Registers the "PaymentToken" interface.
	 *
	 * @return void
	 */
	public static function register_interface() {
		register_graphql_interface_type(
			'PaymentToken',
			[
				'description' => __( 'Payment token object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => function( $value ) {
					$type_registry = \WPGraphQL::get_type_registry();
					$type          = $value->get_type();
					switch ( $type ) {
						case 'CC':
							return $type_registry->get_type( 'PaymentTokenCC' );
						case 'eCheck':
							return $type_registry->get_type( 'PaymentTokenECheck' );
						default:
							throw new UserError(
								sprintf(
									/* translators: %s: Payment token type */
									__( 'The "%s" token type is not supported by the core WPGraphQL WooCommerce (WooGraphQL) schema.', 'wp-graphql-woocommerce' ),
									$type
								)
							);
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
					'description' => __( 'Token ID unique identifier', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						return ! empty( $source->get_id() ) ? Relay::toGlobalId( 'token', $source->get_id() ) : null;
					},
				],
				'tokenId'   => [
					'type'        => [ 'non_null' => 'Integer' ],
					'description' => __( 'Token database ID.', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						return ! empty( $source->get_id() ) ? $source->get_id() : null;
					},
				],
				'type'      => [
					'type'        => [ 'non_null' => 'String' ],
					'description' => __( 'Token type', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						return ! empty( $source->get_type() ) ? $source->get_type() : null;
					},
				],
				'gateway'   => [
					'type'        => 'PaymentGateway',
					'description' => __( 'Token payment gateway', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						$gateway_id = $source->get_gateway_id();
						return null;
					},
				],
				'isDefault' => [
					'type'        => 'Boolean',
					'description' => __( 'Is token connected to user\'s preferred payment method', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						return ! is_null( $source->is_default() ) ? $source->is_default() : false;
					},
				],
			],
			$other_fields
		);
	}
}
