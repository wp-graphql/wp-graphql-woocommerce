<?php
/**
 * Mutation - updateShippingZone
 *
 * Registers mutation for updating a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Shipping_Zone_Update
 */
class Shipping_Zone_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateShippingZone',
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
			'id' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the shipping zone to update.', 'wp-graphql-woocommerce' ),
			],
			'name' => [
				'type'        => 'String',
				'description' => __( 'Name of the shipping zone.', 'wp-graphql-woocommerce' ),
			],
			'order' => [
				'type'        => 'Int',
				'description' => __( 'Order of the shipping zone.', 'wp-graphql-woocommerce' ),
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
			'shippingZone' => [
				'type'    => 'ShippingZone',
				'resolve' => static function ( $payload, array $args, AppContext $context ) {
					return $context->get_loader( 'shipping_zone' )->load( $payload['zone_id'] );
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
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			$zone_id = $input['id'];
			$zone    = \WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );

			if ( false === $zone ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( is_wp_error( $zone ) ) {
				throw new UserError( $zone->get_error_message() );
			}

			if ( 0 === $zone->get_id() ) {
				throw new UserError( __( 'The "locations not covered by your other zones" zone cannot be updated.', 'wp-graphql-woocommerce' ) );
			}

			$zone_changed = false;

			if ( ! empty( $input['name'] ) ) {
				$zone->set_zone_name( $input['name'] );
				$zone_changed = true;
			}

			if ( ! empty( $input['order'] ) ) {
				$zone->set_zone_order( $input['order'] );
				$zone_changed = true;
			}

			if ( $zone_changed ) {
				$zone->save();
			}

			return [ 'zone_id' => $zone_id ];
		};
	}
}
