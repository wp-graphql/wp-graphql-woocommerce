<?php
/**
 * Mutation - updateMethodOnShippingZone
 *
 * Registers mutation for update a shipping method on a shipping zone.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Shipping_Zone_Method_Update
 */
class Shipping_Zone_Method_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateMethodOnShippingZone',
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
			'instanceId' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'Shipping method instance ID', 'wp-graphql-woocommerce' ),
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
			
		};
	}
}
