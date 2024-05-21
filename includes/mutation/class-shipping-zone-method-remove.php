<?php
/**
 * Mutation - removeMethodFromShippingZone
 *
 * Registers mutation for removing a shipping method from a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Shipping_Method;

/**
 * Class - Shipping_Zone_Method_Remove
 */
class Shipping_Zone_Method_Remove {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'removeMethodFromShippingZone',
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
			'zoneId'     => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the shipping zone to delete.', 'wp-graphql-woocommerce' ),
			],
			'instanceId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'Shipping method instance ID', 'wp-graphql-woocommerce' ),
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
			'shippingZone'  => [
				'type'    => 'ShippingZone',
				'resolve' => static function ( $payload, array $args, AppContext $context ) {
					return $context->get_loader( 'shipping_zone' )->load( $payload['zone_id'] );
				},
			],
			'removedMethod' => [
				'type'    => 'ShippingZoneToShippingMethodConnectionEdge',
				'resolve' => static function ( $payload, array $args, AppContext $context ) {
					return [
						// Call the Shipping_Method constructor directly because "$payload['method']" is a non-scalar value.
						'node'   => new Shipping_Method( $payload['method'] ),
						'source' => $context->get_loader( 'shipping_zone' )->load( $payload['zone_id'] ),
					];
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
				throw new UserError( __( 'Sorry, you are not allowed to remove shipping methods', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
			}

			$instance_id = $input['instanceId'];
			$zone_id     = $input['zoneId'];
			/** @var \WC_Shipping_Zone|false $zone */
			$zone = \WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );

			if ( false === $zone ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( 0 === $zone->get_id() ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			$methods = $zone->get_shipping_methods();
			$method  = false;

			foreach ( $methods as $shipping_method ) {
				if ( $shipping_method->instance_id === $instance_id ) {
					$method = $shipping_method;
					break;
				}
			}

			if ( ! $method ) {
				throw new UserError( __( 'Invalid shipping method instance ID.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Filter shipping method object before it's removed from the shipping zone.
			 *
			 * @param \WC_Shipping_Method $method  The shipping method to be deleted.
			 * @param \WC_Shipping_Zone   $zone    The shipping zone object.
			 * @param array               $input   Request input.
			 */
			$method = apply_filters( 'graphql_woocommerce_shipping_zone_method_add', $method, $zone, $input );

			$zone->delete_shipping_method( $instance_id );

			return [
				'zone_id' => $zone_id,
				'zone'    => $zone,
				'method'  => $method,
			];
		};
	}
}
