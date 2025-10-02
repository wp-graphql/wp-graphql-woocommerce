<?php
/**
 * WPObject Type - Shipping_Zone_Type
 *
 * Registers ShippingZone WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.20.0
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
			array(
				'description' => __( 'A Shipping zone object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array(
					'id'         => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the tax rate.', 'wp-graphql-woocommerce' ),
					),
					'databaseId' => array(
						'type'        => 'Int',
						'description' => __( 'The ID of the customer in the database', 'wp-graphql-woocommerce' ),
					),
					'name'       => array(
						'type'        => 'String',
						'description' => __( 'Shipping zone name.', 'wp-graphql-woocommerce' ),
					),
					'order'      => array(
						'type'        => 'Int',
						'description' => __( 'Shipping zone order.', 'wp-graphql-woocommerce' ),
					),
					'locations'  => array(
						'type'        => array( 'list_of' => 'ShippingLocation' ),
						'description' => __( 'Shipping zone locations.', 'wp-graphql-woocommerce' ),
					),
				),
				'connections' => array(
					'methods' => array(
						'toType'     => 'ShippingMethod',
						'edgeFields' => array(
							'id'         => array(
								'type'        => array( 'non_null' => 'ID' ),
								'description' => __( 'The globally unique identifier for the shipping method.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									if ( isset( $edge['node'] ) ) {
										$shipping_method = $edge['node']->as_WC_Data();
										$instance_id     = $shipping_method->instance_id;

										return ! empty( $instance_id ) ? \GraphQLRelay\Relay::toGlobalId( 'shipping_zone_method', $instance_id ) : null;
									}
									return null;
								},
							),
							'instanceId' => array(
								'type'        => 'Int',
								'description' => __( 'Shipping method instance ID.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									if ( isset( $edge['node'] ) ) {
										$shipping_method = $edge['node']->as_WC_Data();
										return $shipping_method->instance_id ?? null;
									}
									return null;
								},
							),
							'order'      => array(
								'type'        => 'Int',
								'description' => __( 'The order of the shipping method.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									if ( isset( $edge['node'] ) ) {
										$shipping_method = $edge['node']->as_WC_Data();
										return $shipping_method->method_order ?? null;
									}
									return null;
								},
							),
							'enabled'    => array(
								'type'        => 'Boolean',
								'description' => __( 'Whether the shipping method is enabled.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									if ( isset( $edge['node'] ) ) {
										/** @var \WC_Shipping_Method $shipping_method */
										$shipping_method = $edge['node']->as_WC_Data();
										return $shipping_method->is_enabled();
									}
									return false;
								},
							),
							'settings'   => array(
								'type'        => array( 'list_of' => 'WCSetting' ),
								'description' => __( 'Shipping method settings.', 'wp-graphql-woocommerce' ),
								'resolve'     => static function ( $edge ) {
									$settings = array();
									if ( isset( $edge['node'] ) ) {
										$shipping_method   = $edge['node']->as_WC_Data();
										$instance_settings = $shipping_method->instance_settings;
										$fields            = $shipping_method->instance_form_fields;
										foreach ( $fields as $key => $field ) {
											$default_value = ! empty( $field['default'] ) ? $field['default'] : null;
											$value         = ! empty( $instance_settings[ $key ] ) ? $instance_settings[ $key ] : $default_value;
											$settings[]    = array_merge(
												$field,
												array(
													'id' => $key,
													'value' => $value,
												)
											);
										}
									}
									return $settings;
								},
							),
						),
						'resolve'    => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
							$resolver = new Shipping_Method_Connection_Resolver( $source, $args, $context, $info );

							return $resolver->get_connection();
						},
					),
				),
			)
		);
	}
}
