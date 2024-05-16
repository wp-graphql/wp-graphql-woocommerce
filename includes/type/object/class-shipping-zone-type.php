<?php
/**
 * WPObject Type - Shipping_Zone_Type
 *
 * Registers ShippingZone WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Shipping_Method_Connection_Resolver;

/**
 * Class Shipping_Zone_Type
 */
class Shipping_Zone_Type {
	/**
	 * Registers shipping zone type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingZone',
			[
				'description' => __( 'A Shipping zone object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The globally unique identifier for the tax rate.', 'wp-graphql-woocommerce' ),
					],
					'databaseId' => [
						'type'        => 'Int',
						'description' => __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' ),
					],
					'name'       => [
                        'type'        => 'String',
                        'description' => __( 'Shipping zone name.', 'wp-graphql-woocommerce' ),
                    ],
                    'order'      => [
                        'type'        => 'Int',
                        'description' => __( 'Shipping zone order.', 'wp-graphql-woocommerce' ),
                    ],
                    'locations'  => [
                        'type'        => [ 'list_of' => 'ShippingLocation' ],
                        'description' => __( 'Shipping zone locations.', 'wp-graphql-woocommerce' ),
                    ],
				],
                'connections' => [
                    'methods' => [
                        'toType'           => 'ShippingMethod',
						'edgeFields'         => [
							'order' => [
								'type'        => 'Int',
								'description' => __( 'The order of the shipping method.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									if ( isset( $edge['node'] ) ) {
										$shipping_method = $edge['node']->as_WC_Data();
										\codecept_debug($shipping_method);
										return $shipping_method->method_order ?? null;
									}
									return null;
								},
							],
							'enabled' => [
								'type'        => 'Boolean',
								'description' => __( 'Whether the shipping method is enabled.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									if ( isset( $edge['node'] ) ) {
										$shipping_method = $edge['node']->as_WC_Data();
										return isset( $shipping_method->enabled ) ? $shipping_method->enabled : false;
									}
									return false;
								},
							],
							'settings' => [
								'type'        => [ 'list_of' => 'WCSetting' ],
								'description' => __( 'Shipping method settings.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									$settings = [];
									if ( isset( $edge['node'] ) ) {
										$shipping_method = $edge['node']->as_WC_Data();
										$fields   = $shipping_method->instance_form_fields;
										foreach ( $fields as $key => $field ) {
											$default_value     = ! empty( $field['default'] ) ? $field['default'] : null;
											$value             = $shipping_method->{$key} ?? $default_value;
											$settings[] = array_merge(
												$field, 
												[
													'id'    => $key,
													'value' => $value,
												]
											);
										}
									}
									return $settings;
								},
							],
						],
                        'resolve'          => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
                            $resolver = new Shipping_Method_Connection_Resolver( $source, $args, $context, $info );

                            return $resolver->get_connection();
                        },
                    ],
                ]
			]
		);
	}
}
