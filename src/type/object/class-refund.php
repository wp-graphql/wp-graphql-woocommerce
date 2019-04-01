<?php
/**
 * WPObject Type - Refund
 *
 * Registers Refund WPObject type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class Refund
 */
class Refund {
	/**
	 * Register Refund type and queries to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'Refund',
			array(
				'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'       => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the refund', 'wp-graphql-woocommerce' ),
					),
					'refundId' => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);

		register_graphql_field(
			'RootQuery',
			'refund',
			array(
				'type'        => 'Refund',
				'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id' => array(
						'type' => array(
							'non_null' => 'ID',
						),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );
					if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}
					$refund_id = absint( $id_components['id'] );
					return Factory::resolve_crud_object( $refund_id, $context );
				},
			)
		);

		$post_by_args = array(
			'id'          => array(
				'type'        => 'ID',
				'description' => __( 'Get the refund by its global ID', 'wp-graphql-woocommerce' ),
			),
			'refundId'    => array(
				'type'        => 'Int',
				'description' => __( 'Get the refund by its database ID', 'wp-graphql-woocommerce' ),
			),
			'orderNumber' => array(
				'type'        => 'String',
				'description' => __( 'Get the refund by its order number', 'wp-graphql-woocommerce' ),
			),
		);

		register_graphql_field(
			'RootQuery',
			'refundBy',
			array(
				'type'        => 'Refund',
				'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
				'args'        => $post_by_args,
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$refund_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}
						$refund_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['refundId'] ) ) {
						$refund_id = absint( $args['refundId'] );
					} elseif ( ! empty( $args['orderNumber'] ) ) {
						$refund_id = 0;
					}

					$refund = Factory::resolve_crud_object( $refund_id, $context );
					if ( get_post( $refund_id )->post_type !== 'shop_order_refund' ) {
						/* translators: not refund found error message */
						throw new UserError( sprintf( __( 'No refund exists with this id: %1$s' ), $refund_id ) );
					}

					return $refund;
				},
			)
		);
	}
}
