<?php
/**
 * WPObject Type - Refund_Type
 *
 * Registers Refund WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Refund_Type
 */
class Refund_Type {

	/**
	 * Register Refund type and queries to the WPGraphQL schema.
	 */
	public static function register() {
		register_graphql_object_type(
			'Refund',
			array(
				'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array(
					'id'         => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the refund', 'wp-graphql-woocommerce' ),
					),
					'refundId'   => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
					),
					'title'      => array(
						'type'        => 'String',
						'description' => __( 'A title for the new post type', 'wp-graphql-woocommerce' ),
					),
					'amount'     => array(
						'type'        => 'Float',
						'description' => __( 'Refunded amount', 'wp-graphql-woocommerce' ),
					),
					'reason'     => array(
						'type'        => 'String',
						'description' => __( 'Reason for refund', 'wp-graphql-woocommerce' ),
					),
					'refundedBy' => array(
						'type'        => 'User',
						'description' => __( 'User who completed the refund', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							return DataSource::resolve_user( $source->refunded_by_id, $context );
						},
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
					'id'     => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The ID for identifying the refund', 'wp-graphql-woocommerce' ),
					),
					'idType' => array(
						'type'        => 'RefundIdTypeEnum',
						'description' => __( 'Type of ID being used identify refund', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context ) {
					$id = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					$refund_id = null;
					switch ( $id_type ) {
						case 'database_id':
							$refund_id = absint( $id );
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $id );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
							}
							$refund_id = absint( $id_components['id'] );
							break;
					}

					if ( empty( $refund_id ) ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No refund ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					} elseif ( get_post( $refund_id )->post_type !== 'shop_order_refund' ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No refund exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					}

					// Check if user authorized to view order.
					$post_type = get_post_type_object( 'shop_order_refund' );
					$is_authorized = current_user_can( $post_type->cap->edit_others_posts );
					if ( get_current_user_id() ) {
						$refund   = \wc_get_order( $refund_id );
						$order_id = $refund->get_parent_id();
						$orders   = wc_get_orders(
							array(
								'type'          => 'shop_order',
								'post__in'      => array( $order_id ),
								'customer_id'   => get_current_user_id(),
								'no_rows_found' => true,
								'return'        => 'ids',
							)
						);

						if ( in_array( $order_id, $orders, true ) ) {
							$is_authorized = true;
						}
					}

					$refund = $is_authorized ? Factory::resolve_crud_object( $refund_id, $context ) : null;

					return $refund;
				},
			)
		);
	}
}
