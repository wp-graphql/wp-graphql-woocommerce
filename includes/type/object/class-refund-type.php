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

use WPGraphQL\AppContext;

/**
 * Class Refund_Type
 */
class Refund_Type {
	/**
	 * Register Refund type and queries to the WPGraphQL schema.
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'Refund',
			[
				'description' => static function () {
					return __( 'A refund object', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => static function () {
							return __( 'The globally unique identifier for the refund', 'graphql-for-ecommerce' );
						},
					],
					'databaseId' => [
						'type'        => 'Int',
						'description' => static function () {
							return __( 'The ID of the refund in the database', 'graphql-for-ecommerce' );
						},
					],
					'title'      => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'A title for the new post type', 'graphql-for-ecommerce' );
						},
					],
					'amount'     => [
						'type'        => 'Float',
						'description' => static function () {
							return __( 'Refunded amount', 'graphql-for-ecommerce' );
						},
					],
					'reason'     => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Reason for refund', 'graphql-for-ecommerce' );
						},
					],
					'refundedBy' => [
						'type'        => 'User',
						'description' => static function () {
							return __( 'User who completed the refund', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $source, array $args, AppContext $context ) {
							$user_id = absint( $source->refunded_by_id );
							if ( 0 !== $user_id ) {
								return $context->get_loader( 'user' )->load( $user_id );
							}
							return null;
						},
					],
					'date'       => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'The date of the refund', 'graphql-for-ecommerce' );
						},
					],

					'metaData'   => Meta_Data_Type::get_metadata_field_definition(),
				],
			]
		);
	}
}
