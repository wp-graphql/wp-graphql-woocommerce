<?php
/**
 * Mutation - deleteShippingZone
 *
 * Registers mutation for deleting a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Shipping_Zone_Delete
 */
class Shipping_Zone_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteShippingZone',
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
				'description' => __( 'The ID of the shipping zone to delete.', 'wp-graphql-woocommerce' ),
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
				'resolve' => static function ( $payload ) {
					return $payload['shippingZone'];
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
			if ( ! \wc_shipping_enabled() ) {
				throw new UserError( __( 'Shipping is disabled.', 'wp-graphql-woocommerce' ), 404 );
			}

			if ( ! \wc_rest_check_manager_permissions( 'settings', 'delete' ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to delete shipping zones', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
			}

			$zone_id = $input['id'];
			/** @var \WC_Shipping_Zone|false $zone */
			$zone = \WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );

			if ( false === $zone ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Filter zone object returned from the GraphQL API.
			 *
			 * @param \WC_Shipping_Zone  $zone   The response object.
			 * @param array              $input  Request input.
			 */
			$zone = apply_filters( 'graphql_woocommerce_delete_shipping_zone', $zone, $input );

			$object = $context->get_loader( 'shipping_zone' )->load( $zone_id );

			/**
			 * Filter zone model returned from the GraphQL API before deletion.
			 *
			 * @param \Shipping_Zone     $object  The response object.
			 * @param \WC_Shipping_Zone  $zone    The zone object.
			 * @param array              $input   Request input.
			 */
			$object = apply_filters( 'graphql_woocommerce_delete_shipping_zone_object', $object, $zone, $input );

			$zone->delete();

			return [ 'shippingZone' => $object ];
		};
	}
}
