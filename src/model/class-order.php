<?php
/**
 * Model - Refund
 *
 * Resolves order crud object model
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Model;

/**
 * Class Order
 */
class Order extends Model {
	/**
	 * Stores the instance of WC_Order
	 *
	 * @var \WC_Order $order
	 * @access protected
	 */
	protected $order;

	/**
	 * Order constructor
	 *
	 * @param int $id - shop_order post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->order               = new \WC_Order( $id );
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

		parent::__construct( 'OrderObject', $this->order, 'list_users', $allowed_restricted_fields, $id );
		$this->init();
	}

	/**
	 * Initializes the Order field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( 'private' === $this->get_visibility() || is_null( $this->order ) ) {
			return null;
		}

		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                  => function() {
					return $this->order->get_id();
				},
				'id'                  => function() {
					return ! empty( $this->order ) ? Relay::toGlobalId( 'shop_order', $this->order->get_id() ) : null;
				},
				'orderId'             => function() {
					return ! empty( $this->order ) ? $this->order->get_id() : null;
				},
				'orderKey'            => function() {
					return ! empty( $this->order ) ? $this->order->get_order_key() : null;
				},
				'currency'            => function() {
					return ! empty( $this->order ) ? $this->order->get_currency() : null;
				},
				'paymentMethod'       => function() {
					return ! empty( $this->order ) ? $this->order->get_payment_method() : null;
				},
				'paymentMethodTitle'  => function() {
					return ! empty( $this->order ) ? $this->order->get_payment_method_title() : null;
				},
				'transactionId'       => function() {
					return ! empty( $this->order ) ? $this->order->get_transaction_id() : null;
				},
				'customerIpAddress'   => function() {
					return ! empty( $this->order ) ? $this->order->get_customer_ip_address() : null;
				},
				'customerUserAgent'   => function() {
					return ! empty( $this->order ) ? $this->order->get_customer_user_agent() : null;
				},
				'createdVia'          => function() {
					return ! empty( $this->order ) ? $this->order->get_created_via() : null;
				},
				'dateCompleted'       => function() {
					return ! empty( $this->order ) ? $this->order->get_date_completed() : null;
				},
				'datePaid'            => function() {
					return ! empty( $this->order ) ? $this->order->get_date_paid() : null;
				},
				'discountTotal'       => function() {
					return ! empty( $this->order ) ? $this->order->get_discount_total() : null;
				},
				'discountTax'         => function() {
					return ! empty( $this->order ) ? $this->order->get_discount_tax() : null;
				},
				'shippingTotal'       => function() {
					return ! empty( $this->order ) ? $this->order->get_shipping_total() : null;
				},
				'shippingTax'         => function() {
					return ! empty( $this->order ) ? $this->order->get_shipping_tax() : null;
				},
				'cartTax'             => function() {
					return ! empty( $this->order ) ? $this->order->get_cart_tax() : null;
				},
				'total'               => function() {
					return ! empty( $this->order ) ? $this->order->get_total() : null;
				},
				'totalTax'            => function() {
					return ! empty( $this->order ) ? $this->order->get_total_tax() : null;
				},
				'subtotal'            => function() {
					return ! empty( $this->order ) ? $this->order->get_subtotal() : null;
				},
				'orderNumber'         => function() {
					return ! empty( $this->order ) ? $this->order->get_order_number() : null;
				},
				'orderVersion'        => function() {
					return ! empty( $this->order ) ? $this->order->get_version() : null;
				},
				'pricesIncludeTax'    => function() {
					return ! empty( $this->order ) ? $this->order->get_prices_include_tax() : null;
				},
				'cartHash'            => function() {
					return ! empty( $this->order ) ? $this->order->get_cart_hash() : null;
				},
				'customerNote'        => function() {
					return ! empty( $this->order ) ? $this->order->get_customer_note() : null;
				},
				'isDownloadPermitted' => function() {
					return ! empty( $this->order ) ? $this->order->is_download_permitted() : null;
				},
				'billing'             => function() {
					return ! empty( $this->order ) ? $this->order->get_address( 'billing' ) : null;
				},
				'shipping'            => function() {
					return ! empty( $this->order ) ? $this->order->get_address( 'shipping' ) : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'customer_id'         => function() {
					return ! empty( $this->order ) ? $this->order->get_customer_id() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
