<?php
/**
 * Mutation - clearShippingZoneLocations
 *
 * Registers mutation for update the registered shipping locations on a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Shipping_Zone_Locations_Update
 */
class Shipping_Zone_Locations_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateShippingZoneLocations',
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
			'zoneId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the shipping zone to delete.', 'wp-graphql-woocommerce' ),
			],
			'locations' => [
				'type' 	  => [ 'list_of' => 'ShippingLocationInput' ],
				'description' => __( 'The locations to add to the shipping zone.', 'wp-graphql-woocommerce' ),
			]
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
				'resolve' => static function ( $payload, array $args, AppContext $context) {
					return $context->get_loader( 'shipping_zone' )->load( $payload['zone_id'] );
				},
			],
            'locations' => [
                'type'    => [ 'list_of' => 'ShippingLocation' ],
                'resolve' => static function ( $payload ) {
                    return ! empty( $payload['locations'] )
						? array_map(
							function( $location ) {
								return (object) $location;
							},
							$payload['locations'],
						)
						: [];
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
			$zone_id = $input['zoneId'];
			$zone    = \WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );

			if ( false === $zone ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( is_wp_error( $zone ) ) {
				throw new UserError( $zone->get_error_message() );
			}

			if ( 0 === $zone->get_id() ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			$raw_locations = ! empty( $input['locations'] ) ? $input['locations'] : [];
			$locations     = [];
			foreach ( $raw_locations as $location ) {
				$type = ! empty( $location['type'] ) ? $location['type'] : 'country';

				$locations[] = [
					'type' => $type,
					'code' => $location['code']
				];
			}

			$zone->set_locations( $locations );
			$zone->save();

			return [
				'zone_id'   => $zone_id,
				'locations' => $locations
			];
		};
	}
}
