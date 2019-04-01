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
	 * Stores the instance of WC_Order_Refund
	 *
	 * @var \WC_Order_Refund $refund
	 * @access protected
	 */
	protected $refund;

	/**
	 * Refund constructor
	 *
	 * @param int $id - shop_order_refund post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->refund              = new \WC_Order_Refund( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'userId',
			'name',
			'firstName',
			'lastName',
			'description',
			'slug',
		];

		parent::__construct( 'RefundObject', $this->refund, 'list_users', $allowed_restricted_fields, $id );
		$this->init();
	}

	/**
	 * Initializes the Refund field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( 'private' === $this->get_visibility() || is_null( $this->refund ) ) {
			return null;
		}

		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'      => function() {
					return $this->refund->get_id();
				},
				'id'      => function() {
					return ! empty( $this->refund ) ? Relay::toGlobalId( 'shop_order', $this->refund->get_id() ) : null;
				},
				'orderId' => function() {
					return ! empty( $this->refund ) ? $this->refund->get_id() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
