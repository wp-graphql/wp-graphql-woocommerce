<?php
/**
 * WPObject Type - Order_Note_Type
 *
 * Registers OrderNote WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
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
				'interfaces'      => ['Node'],
				'eagerlyLoadType' => true,
				'description'     => __( 'A order note', 'wp-graphql-woocommerce' ),
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
					'type'        => ['non_null' => 'ID'],
					'description' => __( 'Database ID or global ID of the order note', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $order_note ) {
						return Relay::toGlobalId( 'order_note', $order_note->ID );
					},
				],
				'databaseId'     => [
					'type'        => 'Int',
					'description' => __( 'Database ID of the order note', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $order_note ) {
						return $order_note->ID;
					},
				],
				'dateCreated'    => [
					'type'        => 'String',
					'description' => __( 'The date the order note was created, in the site\'s timezone.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $order_note ) {
						return $order_note->comment_date_gmt;
					},
				],
				'note'           => [
					'type'        => 'String',
					'description' => __( 'Order note.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $order_note ) {
						return $order_note->comment_content;
					},
				],
				'isCustomerNote' => [
					'type'        => 'Boolean',
					'description' => __( 'Whether the note is a customer note', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $order_note ) {
						return (bool) get_comment_meta( $order_note->comment_ID, 'is_customer_note', true );
					},
				],
			],
			$other_fields
		);
	}
}
