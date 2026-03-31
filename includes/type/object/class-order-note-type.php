<?php
/**
 * WPObject Type - Order_Note_Type
 *
 * Registers OrderNote WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   1.0.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQLRelay\Relay;

/**
 * Class Order_Note_Type
 */
class Order_Note_Type {
	/**
	 * Register Order type and queries to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'OrderNote',
			[
				'interfaces'      => [ 'Node' ],
				'eagerlyLoadType' => true,
				'description'     => static function () {
					return __( 'A order note', 'wp-graphql-woocommerce' );
				},
				'fields'          => apply_filters( 'woographql_order_note_field_definitions', self::get_fields() ),
			]
		);
	}

	/**
	 * Returns the "Order" type fields.
	 *
	 * @param array $other_fields Extra fields configs to be added or override the default field definitions.
	 * @return array
	 */
	public static function get_fields( $other_fields = [] ) {
		return array_merge(
			[
				'id'             => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => static function () {
						return __( 'Database ID or global ID of the order note', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $order_note ) {
						return Relay::toGlobalId( 'order_note', $order_note->databaseId );
					},
				],
				'databaseId'     => [
					'type'        => 'Int',
					'description' => static function () {
						return __( 'Database ID of the order note', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $order_note ) {
						return $order_note->databaseId;
					},
				],
				'dateCreated'    => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'The date the order note was created, in the site\'s timezone.', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $order_note ) {
						return $order_note->date;
					},
				],
				'note'           => [
					'type'        => 'String',
					'description' => static function () {
						return __( 'Order note.', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $order_note ) {
						return $order_note->contentRaw;
					},
				],
				'isCustomerNote' => [
					'type'        => 'Boolean',
					'description' => static function () {
						return __( 'Whether the note is a customer note', 'wp-graphql-woocommerce' );
					},
					'resolve'     => static function ( $order_note ) {
						return (bool) get_comment_meta( $order_note->databaseId, 'is_customer_note', true );
					},
				],
			],
			$other_fields
		);
	}
}
