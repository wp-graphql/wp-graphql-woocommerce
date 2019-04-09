<?php
/**
 * Model - Order
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
class Order extends Crud_CPT {
	/**
	 * Order constructor
	 *
	 * @param int $id - shop_order post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->data                = new \WC_Order( $id );
		$allowed_restricted_fields = array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'orderId',
		);

		parent::__construct( $allowed_restricted_fields, 'shop_order', $id );
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the order
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( post_password_required( $this->data->get_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->data->get_id() ) ) {
			case 'trash':
				$cap = $this->post_type_object->cap->edit_posts;
				break;
			case 'draft':
				$cap = $this->post_type_object->cap->edit_others_posts;
				break;
			default:
				$cap = '';
				break;
		}
		return $cap;
	}

	/**
	 * Initializes the Order field resolvers
	 *
	 * @access protected
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                    => function() {
					return $this->data->get_id();
				},
				'id'                    => function() {
					return ! empty( $this->data ) ? Relay::toGlobalId( 'shop_order', $this->data->get_id() ) : null;
				},
				'orderId'               => function() {
					return ! empty( $this->data ) ? $this->data->get_id() : null;
				},
				'date'                  => function() {
					return ! empty( $this->data ) ? $this->data->get_date_created() : null;
				},
				'modified'              => function() {
					return ! empty( $this->data ) ? $this->data->get_date_modified() : null;
				},
				'orderKey'              => function() {
					return ! empty( $this->data ) ? $this->data->get_order_key() : null;
				},
				'currency'              => function() {
					return ! empty( $this->data ) ? $this->data->get_currency() : null;
				},
				'paymentMethod'         => function() {
					return ! empty( $this->data ) ? $this->data->get_payment_method() : null;
				},
				'paymentMethodTitle'    => function() {
					return ! empty( $this->data ) ? $this->data->get_payment_method_title() : null;
				},
				'transactionId'         => function() {
					return ! empty( $this->data ) ? $this->data->get_transaction_id() : null;
				},
				'customerIpAddress'     => function() {
					return ! empty( $this->data ) ? $this->data->get_customer_ip_address() : null;
				},
				'customerUserAgent'     => function() {
					return ! empty( $this->data ) ? $this->data->get_customer_user_agent() : null;
				},
				'createdVia'            => function() {
					return ! empty( $this->data ) ? $this->data->get_created_via() : null;
				},
				'dateCompleted'         => function() {
					return ! empty( $this->data ) ? $this->data->get_date_completed() : null;
				},
				'datePaid'              => function() {
					return ! empty( $this->data ) ? $this->data->get_date_paid() : null;
				},
				'discountTotal'         => function() {
					return ! empty( $this->data ) ? $this->data->get_discount_total() : null;
				},
				'discountTax'           => function() {
					return ! empty( $this->data ) ? $this->data->get_discount_tax() : null;
				},
				'shippingTotal'         => function() {
					return ! empty( $this->data ) ? $this->data->get_shipping_total() : null;
				},
				'shippingTax'           => function() {
					return ! empty( $this->data ) ? $this->data->get_shipping_tax() : null;
				},
				'cartTax'               => function() {
					return ! empty( $this->data ) ? $this->data->get_cart_tax() : null;
				},
				'total'                 => function() {
					return ! empty( $this->data ) ? $this->data->get_total() : null;
				},
				'totalTax'              => function() {
					return ! empty( $this->data ) ? $this->data->get_total_tax() : null;
				},
				'subtotal'              => function() {
					return ! empty( $this->data ) ? $this->data->get_subtotal() : null;
				},
				'orderNumber'           => function() {
					return ! empty( $this->data ) ? $this->data->get_order_number() : null;
				},
				'orderVersion'          => function() {
					return ! empty( $this->data ) ? $this->data->get_version() : null;
				},
				'pricesIncludeTax'      => function() {
					return ! empty( $this->data ) ? $this->data->get_prices_include_tax() : null;
				},
				'cartHash'              => function() {
					return ! empty( $this->data ) ? $this->data->get_cart_hash() : null;
				},
				'customerNote'          => function() {
					return ! empty( $this->data ) ? $this->data->get_customer_note() : null;
				},
				'isDownloadPermitted'   => function() {
					return ! empty( $this->data ) ? $this->data->is_download_permitted() : null;
				},
				'billing'               => function() {
					return ! empty( $this->data ) ? $this->data->get_address( 'billing' ) : null;
				},
				'shipping'              => function() {
					return ! empty( $this->data ) ? $this->data->get_address( 'shipping' ) : null;
				},
				'status'                => function() {
					return ! empty( $this->data ) ? $this->data->get_status() : null;
				},
				'shippingAddressMapUrl' => function() {
					return ! empty( $this->data ) ? $this->data->get_shipping_address_map_url() : null;
				},
				'hasBillingAddress'     => function() {
					return ! empty( $this->data ) ? $this->data->has_billing_address() : null;
				},
				'hasShippingAddress'    => function() {
					return ! empty( $this->data ) ? $this->data->has_shipping_address() : null;
				},
				'needsShippingAddress'  => function() {
					return ! empty( $this->data ) ? $this->data->needs_shipping_address() : null;
				},
				'hasDownloadableItem'   => function() {
					return ! empty( $this->data ) ? $this->data->has_downloadable_item() : null;
				},
				'needsPayment'          => function() {
					return ! empty( $this->data ) ? $this->data->needs_payment() : null;
				},
				'needsProcessing'       => function() {
					return ! empty( $this->data ) ? $this->data->needs_processing() : null;
				},
				'downloadableItems'     => function() {
					return ! empty( $this->data ) ? $this->data->get_downloadable_items() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'customer_id'           => function() {
					return ! empty( $this->data ) ? $this->data->get_customer_id() : null;
				},
				'parent_id'             => function() {
					return ! empty( $this->data ) ? $this->data->get_parent_id() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
