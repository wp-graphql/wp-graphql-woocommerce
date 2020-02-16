<?php
/**
 * WPObject Type - Shipping_Method_Type
 *
 * Registers ShippingMethod WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\WooCommerce\Data\Factory;

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
				'description' => __( 'A shipping method object', 'wp-graphql-woocommerce' ),
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
						'description' => __( 'The ID for identifying the shipping method', 'wp-graphql-woocommerce' ),
					),
					'idType'   => array(
						'type'        => 'ShippingMethodIdTypeEnum',
						'description' => __( 'Type of ID being used identify product variation', 'wp-graphql-woocommerce' ),
					),
					'methodId' => array(
						'type'              => 'ID',
						'description'       => __( 'Get the shipping method by its database ID', 'wp-graphql-woocommerce' ),
						'isDeprecated'      => true,
						'deprecationReason' => __(
							'This argument has been deprecation, and will be removed in v0.5.x. Please use "shippingMethod(id: value, idType: DATABASE_ID)" instead.',
							'wp-graphql-woocommerce'
						),
					),
				),
				'resolve'     => function ( $source, array $args ) {
					$id = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					/**
					 * Process deprecated arguments
					 *
					 * Will be removed in v0.5.x.
					 */
					if ( ! empty( $args['methodId'] ) ) {
						$id = $args['methodId'];
						$id_type = 'database_id';
					}

					$method_id = null;
					switch ( $id_type ) {
						case 'database_id':
							$method_id = $id;
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $id );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
							}
							$method_id = $id_components['id'];
							break;
					}

					return Factory::resolve_shipping_method( $method_id );
				},
			)
		);
	}
}
