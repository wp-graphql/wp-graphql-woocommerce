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
class Refund extends Crud_CPT {

	/**
	 * Defines get_restricted_cap.
	 */
	use Shop_Manager_Caps;

	/**
	 * Refund constructor.
	 *
	 * @param int $id - shop_order_refund post-type ID.
	 */
	public function __construct( $id ) {
		$this->data                = new WC_Order_Refund( $id );
		$allowed_restricted_fields = array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'refundId',
		);

		parent::__construct( $allowed_restricted_fields, 'shop_order_refund', $id );
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
				'refundId'       => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
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
