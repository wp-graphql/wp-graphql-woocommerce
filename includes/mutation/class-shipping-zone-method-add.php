<?php
/**
 * Mutation - addMethodToShippingZone
 *
 * Registers mutation for adding a shipping method to a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Shipping_Mutation;
use WPGraphQL\WooCommerce\Model\Shipping_Method;

/**
 * Class - Shipping_Zone_Method_Add
 */
class Shipping_Zone_Method_Add {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'addMethodToShippingZone',
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
			'zoneId'   => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'The ID of the shipping zone to delete.', 'graphql-for-ecommerce' );
				},
			],
			'methodId' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'The ID of the shipping method to add.', 'graphql-for-ecommerce' );
				},
			],
			'enabled'  => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Whether the shipping method is enabled or not.', 'graphql-for-ecommerce' );
				},
			],
			'order'    => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'The order of the shipping method.', 'graphql-for-ecommerce' );
				},
			],
			'settings' => [
				'type'        => [ 'list_of' => 'WCSettingInput' ],
				'description' => static function () {
					return __( 'The settings for the shipping method.', 'graphql-for-ecommerce' );
				},
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
			'method'       => [
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
				throw new UserError( __( 'Shipping is disabled.', 'graphql-for-ecommerce' ), 404 );
			}

			if ( ! \wc_rest_check_manager_permissions( 'settings', 'edit' ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to add shipping methods', 'graphql-for-ecommerce' ), \rest_authorization_required_code() );
			}

			$method_id = $input['methodId'];
			$zone_id   = $input['zoneId'];
			/** @var \WC_Shipping_Zone|false $zone */
			$zone = \WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );

			if ( false === $zone ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'graphql-for-ecommerce' ) );
			}

			if ( 0 === $zone->get_id() ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'graphql-for-ecommerce' ) );
			}

			$instance_id = $zone->add_shipping_method( $method_id );
			$methods     = $zone->get_shipping_methods();
			$method      = false;
			foreach ( $methods as $method_obj ) {
				if ( $method_obj->instance_id === $instance_id ) {
					$method = $method_obj;
					break;
				}
			}

			if ( false === $method ) {
				throw new UserError( __( 'Failed to add shipping method to shipping zone.', 'graphql-for-ecommerce' ) );
			}

			// Update settings.
			if ( ! empty( $input['settings'] ) ) {
				$method = Shipping_Mutation::set_shipping_zone_method_settings( $instance_id, $method, $input['settings'] );
			}

			// Update order.
			if ( isset( $input['order'] ) ) {
				$method = Shipping_Mutation::set_shipping_zone_method_order( $instance_id, $method, $input['order'] );
			}

			// Update if this method is enabled or not.
			if ( isset( $input['enabled'] ) ) {
				$method = Shipping_Mutation::set_shipping_zone_method_enabled( $zone_id, $instance_id, $method, $input['enabled'] );
			}

			/**
			 * Filter shipping method object before responding.
			 *
			 * @param \WC_Shipping_Method $method  The shipping method object.
			 * @param \WC_Shipping_Zone   $zone    The response object.
			 * @param array               $input   Request input.
			 */
			$method = apply_filters( 'graphql_woocommerce_shipping_zone_method_add', $method, $zone, $input );

			return [
				'zone_id' => $zone_id,
				'zone'    => $zone,
				'method'  => $method,
			];
		};
	}
}
