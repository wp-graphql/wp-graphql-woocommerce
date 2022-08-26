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
			[
				'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'The globally unique identifier for the refund', 'wp-graphql-woocommerce' ),
					],
					'databaseId' => [
						'type'        => 'Int',
						'description' => __( 'The ID of the refund in the database', 'wp-graphql-woocommerce' ),
					],
					'title'      => [
						'type'        => 'String',
						'description' => __( 'A title for the new post type', 'wp-graphql-woocommerce' ),
					],
					'amount'     => [
						'type'        => 'Float',
						'description' => __( 'Refunded amount', 'wp-graphql-woocommerce' ),
					],
					'reason'     => [
						'type'        => 'String',
						'description' => __( 'Reason for refund', 'wp-graphql-woocommerce' ),
					],
					'refundedBy' => [
						'type'        => 'User',
						'description' => __( 'User who completed the refund', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context ) {
							return DataSource::resolve_user( $source->refunded_by_id, $context );
						},
					],
					'date'       => [
						'type'        => 'String',
						'description' => __( 'The date of the refund', 'wp-graphql-woocommerce' ),
					],

					'metaData'   => Meta_Data_Type::get_metadata_field_definition(),
				],
			]
		);
	}
}
