<?php
/**
 * Model - Refund
 *
 * Resolves refund crud object model
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Model;

/**
 * Class Refund
 */
class Refund extends Model {
	/**
	 * Refund constructor
	 *
	 * @param int $id - shop_order_refund post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->data                = new \WC_Order_Refund( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'refundId',
		];

		parent::__construct( 'list_users', $allowed_restricted_fields, $id );
	}

	/**
	 * Initializes the Refund field resolvers
	 *
	 * @access protected
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'             => function() {
					return $this->data->get_id();
				},
				'id'             => function() {
					return ! empty( $this->data ) ? Relay::toGlobalId( 'shop_order', $this->data->get_id() ) : null;
				},
				'refundId'       => function() {
					return ! empty( $this->data ) ? $this->data->get_id() : null;
				},
				'title'          => function() {
					return ! empty( $this->data ) ? $this->data->get_post_title() : null;
				},
				'amount'         => function() {
					return ! empty( $this->data ) ? $this->data->get_amount() : null;
				},
				'reason'         => function() {
					return ! empty( $this->data ) ? $this->data->get_reason() : null;
				},
				'refunded_by_id' => array(
					'callback'   => function() {
						return ! empty( $this->data ) ? $this->data->get_refunded_by() : null;
					},
					'capability' => 'list_users',
				),
			);
		}

		parent::prepare_fields();
	}
}
