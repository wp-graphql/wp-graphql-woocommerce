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
class Order extends WC_Post {

	/**
	 * Hold order post type slug
	 *
	 * @var string $post_type
	 */
	protected $post_type = 'shop_order';

	/**
	 * Order constructor.
	 *
	 * @param int $id - shop_order post-type ID.
	 */
	public function __construct( $id ) {
		$data = $this->get_object( $id );
		parent::__construct( $data );
	}

	/**
	 * Return the data source to be used by the model.
	 *
	 * @param integer $id  Order ID.
	 *
	 * @return WC_Data
	 */
	protected function get_object( $id ) {
		return \WC_Order_Factory::get_order( $id );
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the order
	 *
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( ! empty( $this->data->post_password ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( $this->data->post_status ) {
			case 'trash':
				$cap = $this->post_type_object->cap->edit_posts;
				break;
			case 'draft':
			case 'future':
			case 'pending':
				$cap = $this->post_type_object->cap->edit_others_posts;
				break;
			default:
				$cap = '';
				if ( ! $this->owner_matches_current_user() ) {
					$cap = $this->post_type_object->cap->edit_posts;
				}
				break;
		}

		return $cap;
	}

	/**
	 * Whether or not the customer of the order matches the current user.
	 *
	 * @return bool
	 */
	protected function owner_matches_current_user() {
		// Get Customer ID.
		if ( 'shop_order' === $this->post_type ) {
			$customer_id = $this->wc_data->get_customer_id();
		} else {
			$customer_id = get_post_meta( '_customer_user', $this->wc_data->get_parent_id(), true );
		}

		if ( 0 === $customer_id ) {
			return $this->guest_order_customer_matches_current_user();
		}

		if ( empty( $this->current_user->ID ) || empty( $customer_id ) ) {
			return false;
		}

		return absint( $customer_id ) === absint( $this->current_user->ID ) ? true : false;
	}

	/**
	 * Whether or not the customer of the order who is a guest matches the current user.
	 *
	 * @return bool
	 */
	public function guest_order_customer_matches_current_user() {
		if ( 'shop_order' === $this->post_type ) {
			$customer_email = $this->wc_data->get_billing_email();
		} else {
			$customer_email = get_post_meta( '_billing_email', $this->wc_data->get_parent_id(), true );
		}

		$session_customer = new \WC_Customer( 0, true );
		if ( empty( $session_customer->get_billing_email() ) || empty( $customer_email ) ) {
			return false;
		}

		return $customer_email === $session_customer->get_billing_email() ? true : false ;
	}

	/**
	 * Determine if the model is private
	 *
	 * @return bool
	 */
	public function is_private() {
		/**
		 * Published content is public, not private
		 */
		if ( wc_is_order_status( $this->data->post_status ) ) {
			return false;
		}

		return $this->is_post_private( $this->data );
	}

	/**
	 * Return the fields that visible to owners of the order without management caps.
	 *
	 * @param array  $allowed_restricted_fields  The fields to allow when the data is designated as restricted to the current user
	 *
	 * @return array
	 */
	protected static function get_allowed_restricted_fields( $allowed_restricted_fields = array() ) {
		return array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
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
			'downloadable_items',
			'commentCount',
			'commentStatus',
		);
	}

	/**
	 * Initializes the Order field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			parent::init();

			$fields = array(
				'id'                    => function() {
					return ! empty( $this->wc_data->get_id() ) ? Relay::toGlobalId( 'shop_order', $this->wc_data->get_id() ) : null;
				},
				'date'                  => function() {
					return ! empty( $this->wc_data->get_date_created() ) ? $this->wc_data->get_date_created() : null;
				},
				'modified'              => function() {
					return ! empty( $this->wc_data->get_date_modified() ) ? $this->wc_data->get_date_modified() : null;
				},
				'orderKey'              => function() {
					return ! empty( $this->wc_data->get_order_key() ) ? $this->wc_data->get_order_key() : null;
				},
				'currency'              => function() {
					return ! empty( $this->wc_data->get_currency() ) ? $this->wc_data->get_currency() : null;
				},
				'paymentMethod'         => function() {
					return ! empty( $this->wc_data->get_payment_method() ) ? $this->wc_data->get_payment_method() : null;
				},
				'paymentMethodTitle'    => function() {
					return ! empty( $this->wc_data->get_payment_method_title() ) ? $this->wc_data->get_payment_method_title() : null;
				},
				'transactionId'         => function() {
					return ! empty( $this->wc_data->get_transaction_id() ) ? $this->wc_data->get_transaction_id() : null;
				},
				'customerIpAddress'     => function() {
					return ! empty( $this->wc_data->get_customer_ip_address() ) ? $this->wc_data->get_customer_ip_address() : null;
				},
				'customerUserAgent'     => function() {
					return ! empty( $this->wc_data->get_customer_user_agent() ) ? $this->wc_data->get_customer_user_agent() : null;
				},
				'createdVia'            => function() {
					return ! empty( $this->wc_data->get_created_via() ) ? $this->wc_data->get_created_via() : null;
				},
				'dateCompleted'         => function() {
					return ! empty( $this->wc_data->get_date_completed() ) ? $this->wc_data->get_date_completed() : null;
				},
				'datePaid'              => function() {
					return ! empty( $this->wc_data->get_date_paid() ) ? $this->wc_data->get_date_paid() : null;
				},
				'discountTotal'         => function() {
					$price = ! empty( $this->wc_data->get_discount_total() ) ? $this->wc_data->get_discount_total() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'discountTotalRaw'      => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_discount_total() ) ? $this->wc_data->get_discount_total() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'discountTax'           => function() {
					$price = ! empty( $this->wc_data->get_discount_tax() ) ? $this->wc_data->get_discount_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'discountTaxRaw'        => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_discount_tax() ) ? $this->wc_data->get_discount_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'shippingTotal'         => function() {
					$price = ! empty( $this->wc_data->get_shipping_total() ) ? $this->wc_data->get_shipping_total() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'shippingTotalRaw'      => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_shipping_total() ) ? $this->wc_data->get_shipping_total() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'shippingTax'           => function() {
					$price = ! empty( $this->wc_data->get_shipping_tax() ) ? $this->wc_data->get_shipping_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'shippingTaxRaw'        => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_shipping_tax() ) ? $this->wc_data->get_shipping_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'cartTax'               => function() {
					$price = ! empty( $this->wc_data->get_cart_tax() ) ? $this->wc_data->get_cart_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'cartTaxRaw'            => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_cart_tax() ) ? $this->wc_data->get_cart_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'total'                 => function() {
					$price = ! empty( $this->wc_data->get_total() ) ? $this->wc_data->get_total() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'totalRaw'              => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_total() ) ? $this->wc_data->get_total() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'totalTax'              => function() {
					$price = ! empty( $this->wc_data->get_total_tax() ) ? $this->wc_data->get_total_tax() : 0;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'totalTaxRaw'           => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_total_tax() ) ? $this->wc_data->get_total_tax() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'subtotal'              => function() {
					$price = ! empty( $this->wc_data->get_subtotal() ) ? $this->wc_data->get_subtotal() : null;
					return \wc_graphql_price( $price, array( 'currency' => $this->wc_data->get_currency() ) );
				},
				'subtotalRaw'           => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_subtotal() ) ? $this->wc_data->get_subtotal() : 0;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'orderNumber'           => function() {
					return ! empty( $this->wc_data->get_order_number() ) ? $this->wc_data->get_order_number() : null;
				},
				'orderVersion'          => function() {
					return ! empty( $this->wc_data->get_version() ) ? $this->wc_data->get_version() : null;
				},
				'pricesIncludeTax'      => function() {
					return ! is_null( $this->wc_data->get_prices_include_tax() ) ? $this->wc_data->get_prices_include_tax() : null;
				},
				'cartHash'              => function() {
					return ! empty( $this->wc_data->get_cart_hash() ) ? $this->wc_data->get_cart_hash() : null;
				},
				'customerNote'          => function() {
					return ! empty( $this->wc_data->get_customer_note() ) ? $this->wc_data->get_customer_note() : null;
				},
				'isDownloadPermitted'   => function() {
					return ! is_null( $this->wc_data->is_download_permitted() ) ? $this->wc_data->is_download_permitted() : null;
				},
				'billing'               => function() {
					return ! empty( $this->wc_data->get_address( 'billing' ) ) ? $this->wc_data->get_address( 'billing' ) : null;
				},
				'shipping'              => function() {
					return ! empty( $this->wc_data->get_address( 'shipping' ) ) ? $this->wc_data->get_address( 'shipping' ) : null;
				},
				'status'                => function() {
					return ! empty( $this->wc_data->get_status() ) ? $this->wc_data->get_status() : null;
				},
				'shippingAddressMapUrl' => function() {
					return ! empty( $this->wc_data->get_shipping_address_map_url() ) ? $this->wc_data->get_shipping_address_map_url() : null;
				},
				'hasBillingAddress'     => function() {
					return ! is_null( $this->wc_data->has_billing_address() ) ? $this->wc_data->has_billing_address() : null;
				},
				'hasShippingAddress'    => function() {
					return ! is_null( $this->wc_data->has_shipping_address() ) ? $this->wc_data->has_shipping_address() : null;
				},
				'needsShippingAddress'  => function() {
					return ! is_null( $this->wc_data->needs_shipping_address() ) ? $this->wc_data->needs_shipping_address() : null;
				},
				'hasDownloadableItem'   => function() {
					return ! is_null( $this->wc_data->has_downloadable_item() ) ? $this->wc_data->has_downloadable_item() : null;
				},
				'needsPayment'          => function() {
					return ! is_null( $this->wc_data->needs_payment() ) ? $this->wc_data->needs_payment() : null;
				},
				'needsProcessing'       => function() {
					return ! is_null( $this->wc_data->needs_processing() ) ? $this->wc_data->needs_processing() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'customer_id'           => function() {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_customer_id() : null;
				},
				'parent_id'             => function() {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_parent_id() : null;
				},
				'downloadable_items'    => function() {
					return ! empty( $this->wc_data->get_downloadable_items() ) ? $this->wc_data->get_downloadable_items() : null;
				},
				/**
				 * Defines aliased fields
				 *
				 * These fields are used primarily by WPGraphQL core Node* interfaces
			 	 * and some fields act as aliases/decorator for existing fields.
				 */
				'commentCount'    => function() {
					remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

					$args  = array(
						'post_id' => $this->ID,
						'approve' => 'approve',
						'fields'  => 'ids',
						'type'    => '',
					);

					if ( ! current_user_can( $this->post_type_object->cap->edit_posts, $this->ID ) ) {
						$args += array(
							'meta_key'   => 'is_customer_note',
							'meta_value' => true,
						);
					}

					$notes = get_comments( $args );

					add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

					return count( $notes );
				},
				'commentStatus'   => function() {
					return current_user_can( $this->post_type_object->cap->edit_posts, $this->ID ) ?  'open' : 'closed';
				},
			);

			$this->fields = array_merge( $this->fields, $fields );
		}
	}
}
