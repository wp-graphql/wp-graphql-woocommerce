<?php
/**
 * Mutation - addMethodToShippingZone
 *
 * Registers mutation for adding a shipping method to a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

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
			'zoneId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the shipping zone to delete.', 'wp-graphql-woocommerce' ),
			],
			'methodId' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'The ID of the shipping method to add.', 'wp-graphql-woocommerce' ),
			],
			'enabled' => [
				'type'    => 'Boolean',
				'description' => __( 'Whether the shipping method is enabled or not.', 'wp-graphql-woocommerce' ),
			],
			'order' => [
				'type'    => 'Int',
				'description' => __( 'The order of the shipping method.', 'wp-graphql-woocommerce' ),
			],
			'settings' => [
				'type'    => [ 'list_of' => 'WCSettingInput' ],
				'description' => __( 'The settings for the shipping method.', 'wp-graphql-woocommerce' ),
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
					
				},
			],
            'method' => [
                'type'    => 'ShippingMethod',
                'resolve' => static function ( $payload ) {
                    
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
			$method_id = $input['methodId'];
			$zone_id   = $input['zoneId'];
			$zone      = \WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );

			if ( false === $zone ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( is_wp_error( $zone ) ) {
				throw new UserError( $zone->get_error_message() );
			}

			if ( 0 === $zone->get_id() ) {
				throw new UserError( __( 'Invalid shipping zone ID.', 'wp-graphql-woocommerce' ) );
			}

			$instance_id = $zone->add_shipping_method( $method_id );
			$methods     = $zone->get_shipping_methods();
			$method	     = false;
			foreach ( $methods as $method_obj ) {
				if ( $method_obj->instance_id === $instance_id ) {
					$method = $method_obj;
					break;
				}
			}

			if ( false === $method ) {
				throw new UserError( __( 'Failed to add shipping method to shipping zone.', 'wp-graphql-woocommerce' ) );
			}

			self::update_fields( $instance_id, $method, $input );
		};
	}

	/**
	 * Updates settings, order, and enabled status on create. 
	 *
	 * @param [type] $instance_id  Instance ID of the shipping method.
	 * @param [type] $method       Shipping method object.
	 * @param [type] $input        Input data.
	 *
	 * @return void
	 */
	public static function update_fields( $instance_id, $method, $input) {
		global $wpdb;

		if ( ! empty( $input['settings'] ) ) {
			$method->init_instance_settings();
			$instance_settings = $method->instance_settings;
			$errors_found      = false;
			foreach ( $method->get_instance_form_fields() as $key => $field ) {
				if ( isset( $input['settings'][ $key ] ) ) {
					if ( is_callable( array( self::class, 'validate_setting_' . $field['type'] . '_field' ) ) ) {
						$value = self::class::{'validate_setting_' . $field['type'] . '_field'}( $input['settings'][ $key ], $field );
					} else {
						$value = self::class::validate_setting_text_field( $input['settings'][ $key ], $field );
					}
					if ( is_wp_error( $value ) ) {
						throw new UserError( $value->get_error_message() );
					}
					$instance_settings[ $key ] = $value;
				}
			}

			update_option( $method->get_instance_option_key(), apply_filters( 'woocommerce_shipping_' . $method->id . '_instance_settings_values', $instance_settings, $method ) );
		}

		// Update order.
		if ( isset( $input['order'] ) ) {
			$wpdb->update( "{$wpdb->prefix}woocommerce_shipping_zone_methods", [ 'method_order' => $input['order'] ], [ 'instance_id' => $instance_id ] );
			$method->method_order = absint( $input['order'] );
		}

		// Update if this method is enabled or not.
		if ( isset( $input['enabled'] ) ) {
			if ( $wpdb->update( "{$wpdb->prefix}woocommerce_shipping_zone_methods", [ 'is_enabled' => $input['enabled'] ], [ 'instance_id' => $instance_id ] ) ) {
				do_action( 'woocommerce_shipping_zone_method_status_toggled', $instance_id, $method->id, $input['zoneId'], $request['enabled'] );
				$method->enabled = ( true === $input['enabled'] ? 'yes' : 'no' );
			}
		}

		return $method;

	}
}
