<?php
/**
 * Model - Order
 *
 * Resolves order crud object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;

/**
 * Class Order
 */
class Order extends Crud_CPT {

	/**
	 * Defines get_restricted_cap
	 */
	use Shop_Manager_Caps;

	/**
	 * Order constructor.
	 *
	 * @param int $id - shop_order post-type ID.
	 */
	public function __construct( $id ) {
		$this->data                = \WC_Order_Factory::get_order( $id );
		$allowed_restricted_fields = array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'orderId',
			'orderNumber',
			'status',
			'date',
			'modified',
			'datePaid',
			'dateCompleted',
			'paymentMethodTitle',
			'customerNote',
			'billing',
			'shipping',
			'discountTotal',
			'discountTax',
			'shippingTotal',
			'shippingTax',
			'cartTax',
			'subtotal',
			'total',
			'totalTax',
			'isDownloadPermitted',
			'shippingAddressMapUrl',
			'needsShippingAddress',
			'needsPayment',
			'needsProcessing',
			'hasDownloadableItem',
			'downloadableItems',
		);

		parent::__construct( $allowed_restricted_fields, 'shop_order', $id );
	}

	/**
	 * Initializes the Order field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                    => function() {
					return $this->data->get_id();
				},
				'id'                    => function() {
					return ! empty( $this->data->get_id() ) ? Relay::toGlobalId( 'shop_order', $this->data->get_id() ) : null;
				},
				'orderId'               => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
				},
				'date'                  => function() {
					return ! empty( $this->data->get_date_created() ) ? $this->data->get_date_created() : null;
				},
				'modified'              => function() {
					return ! empty( $this->data->get_date_modified() ) ? $this->data->get_date_modified() : null;
				},
				'orderKey'              => function() {
					return ! empty( $this->data->get_order_key() ) ? $this->data->get_order_key() : null;
				},
				'currency'              => function() {
					return ! empty( $this->data->get_currency() ) ? $this->data->get_currency() : null;
				},
				'paymentMethod'         => function() {
					return ! empty( $this->data->get_payment_method() ) ? $this->data->get_payment_method() : null;
				},
				'paymentMethodTitle'    => function() {
					return ! empty( $this->data->get_payment_method_title() ) ? $this->data->get_payment_method_title() : null;
				},
				'transactionId'         => function() {
					return ! empty( $this->data->get_transaction_id() ) ? $this->data->get_transaction_id() : null;
				},
				'customerIpAddress'     => function() {
					return ! empty( $this->data->get_customer_ip_address() ) ? $this->data->get_customer_ip_address() : null;
				},
				'customerUserAgent'     => function() {
					return ! empty( $this->data->get_customer_user_agent() ) ? $this->data->get_customer_user_agent() : null;
				},
				'createdVia'            => function() {
					return ! empty( $this->data->get_created_via() ) ? $this->data->get_created_via() : null;
				},
				'dateCompleted'         => function() {
					return ! empty( $this->data->get_date_completed() ) ? $this->data->get_date_completed() : null;
				},
				'datePaid'              => function() {
					return ! empty( $this->data->get_date_paid() ) ? $this->data->get_date_paid() : null;
				},
				'discountTotal'         => function() {
					$price = ! empty( $this->data->get_discount_total() ) ? $this->data->get_discount_total() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'discountTotalRaw'      => array(
					'callback'   => function() {
						return ! empty( $this->data->get_discount_total() ) ? $this->data->get_discount_total() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'discountTax'           => function() {
					$price = ! empty( $this->data->get_discount_tax() ) ? $this->data->get_discount_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'discountTaxRaw'        => array(
					'callback'   => function() {
						return ! empty( $this->data->get_discount_tax() ) ? $this->data->get_discount_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'shippingTotal'         => function() {
					$price = ! empty( $this->data->get_shipping_total() ) ? $this->data->get_shipping_total() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'shippingTotalRaw'      => array(
					'callback'   => function() {
						return ! empty( $this->data->get_shipping_total() ) ? $this->data->get_shipping_total() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'shippingTax'           => function() {
					$price = ! empty( $this->data->get_shipping_tax() ) ? $this->data->get_shipping_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'shippingTaxRaw'        => array(
					'callback'   => function() {
						return ! empty( $this->data->get_shipping_tax() ) ? $this->data->get_shipping_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'cartTax'               => function() {
					$price = ! empty( $this->data->get_cart_tax() ) ? $this->data->get_cart_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'cartTaxRaw'            => array(
					'callback'   => function() {
						return ! empty( $this->data->get_cart_tax() ) ? $this->data->get_cart_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'total'                 => function() {
					$price = ! empty( $this->data->get_total() ) ? $this->data->get_total() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'totalRaw'              => array(
					'callback'   => function() {
						return ! empty( $this->data->get_total() ) ? $this->data->get_total() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'totalTax'              => function() {
					$price = ! empty( $this->data->get_total_tax() ) ? $this->data->get_total_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'totalTaxRaw'           => array(
					'callback'   => function() {
						return ! empty( $this->data->get_total_tax() ) ? $this->data->get_total_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'subtotal'              => function() {
					$price = ! empty( $this->data->get_subtotal() ) ? $this->data->get_subtotal() : null;
					return \wc_graphql_price( $price, array( 'currency' => $this->data->get_currency() ) );
				},
				'subtotalRaw'           => array(
					'callback'   => function() {
						return ! empty( $this->data->get_subtotal() ) ? $this->data->get_subtotal() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'orderNumber'           => function() {
					return ! empty( $this->data->get_order_number() ) ? $this->data->get_order_number() : null;
				},
				'orderVersion'          => function() {
					return ! empty( $this->data->get_version() ) ? $this->data->get_version() : null;
				},
				'pricesIncludeTax'      => function() {
					return ! is_null( $this->data->get_prices_include_tax() ) ? $this->data->get_prices_include_tax() : null;
				},
				'cartHash'              => function() {
					return ! empty( $this->data->get_cart_hash() ) ? $this->data->get_cart_hash() : null;
				},
				'customerNote'          => function() {
					return ! empty( $this->data->get_customer_note() ) ? $this->data->get_customer_note() : null;
				},
				'isDownloadPermitted'   => function() {
					return ! is_null( $this->data->is_download_permitted() ) ? $this->data->is_download_permitted() : null;
				},
				'billing'               => function() {
					return ! empty( $this->data->get_address( 'billing' ) ) ? $this->data->get_address( 'billing' ) : null;
				},
				'shipping'              => function() {
					return ! empty( $this->data->get_address( 'shipping' ) ) ? $this->data->get_address( 'shipping' ) : null;
				},
				'status'                => function() {
					return ! empty( $this->data->get_status() ) ? $this->data->get_status() : null;
				},
				'shippingAddressMapUrl' => function() {
					return ! empty( $this->data->get_shipping_address_map_url() ) ? $this->data->get_shipping_address_map_url() : null;
				},
				'hasBillingAddress'     => function() {
					return ! is_null( $this->data->has_billing_address() ) ? $this->data->has_billing_address() : null;
				},
				'hasShippingAddress'    => function() {
					return ! is_null( $this->data->has_shipping_address() ) ? $this->data->has_shipping_address() : null;
				},
				'needsShippingAddress'  => function() {
					return ! is_null( $this->data->needs_shipping_address() ) ? $this->data->needs_shipping_address() : null;
				},
				'hasDownloadableItem'   => function() {
					return ! is_null( $this->data->has_downloadable_item() ) ? $this->data->has_downloadable_item() : null;
				},
				'needsPayment'          => function() {
					return ! is_null( $this->data->needs_payment() ) ? $this->data->needs_payment() : null;
				},
				'needsProcessing'       => function() {
					return ! is_null( $this->data->needs_processing() ) ? $this->data->needs_processing() : null;
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
				'downloadable_items'    => function() {
					return ! empty( $this->data->get_downloadable_items() ) ? $this->data->get_downloadable_items() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
