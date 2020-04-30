<?php
/**
 * Model - Refund
 *
 * Resolves refund crud object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WC_Order_Refund;

/**
 * Class Refund.
 */
class Refund extends Order {

	/**
	 * Hold order post type slug
	 * 
	 * @var string $post_type
	 */
	protected $post_type = 'shop_order_refund';

	/**
	 * Return the data source to be used by the model.
	 * 
	 * @param integer $id  Refund ID.
	 * 
	 * @return WC_Data
	 */
	protected function get_object( $id ) {
		return new WC_Order_Refund( $id );
	}

	/**
	 * Return the fields allowed to be displayed even if this entry is restricted.
	 *
	 * @return array
	 */
	protected function get_allowed_restricted_fields() {
		return array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		);
	}

	/**
	 * Initializes the Refund field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'             => function() {
					return $this->data->get_id();
				},
				'id'             => function() {
					return ! empty( $this->data->get_id() ) ? Relay::toGlobalId( 'shop_order_refund', $this->data->get_id() ) : null;
				},
				'databaseId'     => function() {
					return $this->ID ?? $this->data->get_id();
				},
				'title'          => function() {
					return ! empty( $this->data->get_post_title() ) ? $this->data->get_post_title() : null;
				},
				'amount'         => function() {
					return ! empty( $this->data->get_amount() ) ? $this->data->get_amount() : null;
				},
				'reason'         => function() {
					return ! empty( $this->data->get_reason() ) ? $this->data->get_reason() : null;
				},
				'refunded_by_id' => array(
					'callback'   => function() {
						return ! empty( $this->data->get_refunded_by() ) ? $this->data->get_refunded_by() : null;
					},
					'capability' => 'list_users',
				),
			);
		}

		parent::prepare_fields();
	}
}
