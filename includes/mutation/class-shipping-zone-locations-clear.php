<?php
/**
 * Mutation - clearShippingZoneLocations
 *
 * Registers mutation for removing all registered shipping locations from a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Shipping_Zone_Locations_Clear
 */
class Shipping_Zone_Locations_Clear {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'clearShippingZoneLocations',
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
			'type' => [
				'type'        => 'ShippingLocationTypeEnum',
				'description' => __( 'The type of location to remove.', 'wp-graphql-woocommerce' ),
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
				'resolve' => static function ( $payload, array $args, AppContext $context) {
					return $context->get_loader( 'shipping_zone' )->load( $payload['zone_id'] );
				},
			],
            'removedLocations' => [
                'type'    => [ 'list_of' => 'ShippingLocation' ],
                'resolve' => static function ( $payload ) {
                    return ! empty( $payload['removedLocations'] )
						? array_map(
							function( $location ) {
								return (object) $location;
							},
							$payload['removedLocations'],
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

			

			$types = [ 'postcode', 'state', 'country', 'continent' ];
			if ( ! empty( $input['type'] ) ) {
				$types = [ $input['type'] ];
			}

			$all_locations = $zone->get_zone_locations();
			$locations     = array_filter(
				$all_locations,
				function( $location ) use ( $types ) {
					return in_array( $location->type, $types, true );
				}
			);

			$zone->clear_locations( $types );
			$zone->save();

			return [
				'zone_id'          => $zone_id,
				'removedLocations' => $locations,
			];
		};
	}
}
