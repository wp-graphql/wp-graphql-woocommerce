<?php
/**
 * WPObject Type - Shipping_Method_Type
 *
 * Registers ShippingMethod WPObject type and queries
 *
 * @package \WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Model\Shipping_Method;
use WPGraphQL\Type\WPObjectType;

/**
 * Class Shipping_Method_Type
 */
class Shipping_Method_Type {
	/**
	 * Registers shipping method type
	 */
	public static function register() {
		register_graphql_object_type(
			'ShippingMethod',
			array(
				'description' => __( 'A shipping method object', 'wp-graphql-woocommercer' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array(
					'id'          => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the tax rate.', 'wp-graphql-woocommerce' ),
					),
					'methodId'    => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The ID of the shipping method.', 'wp-graphql-woocommerce' ),
					),
					'title'       => array(
						'type'        => 'String',
						'description' => __( 'Shipping method title.', 'wp-graphql-woocommerce' ),
					),
					'description' => array(
						'type'        => 'String',
						'description' => __( 'Shipping method description.', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);

		register_graphql_field(
			'RootQuery',
			'shippingMethod',
			array(
				'type'        => 'ShippingMethod',
				'description' => __( 'A shipping method object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'       => array(
						'type'        => 'ID',
						'description' => __( 'Get the shipping method by its global ID', 'wp-graphql-woocommerce' ),
					),
					'methodId' => array(
						'type'        => 'ID',
						'description' => __( 'Get the shipping method by its database ID', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args ) {
					$method_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}

						$arg          = 'ID';
						$method_id = $id_components['id'];
					} elseif ( ! empty( $args['methodId'] ) ) {
						$arg          = 'database ID';
						$method_id = $args['methodId'];
					}

					return Factory::resolve_shipping_method( $method_id );
				},
			)
		);
	}
}
