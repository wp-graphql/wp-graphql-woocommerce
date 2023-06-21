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

use Automattic\WooCommerce\Utilities\OrderUtil;
use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\Model\Model;

/**
 * Class Order
 *
 * @property int           $ID
 * @property string        $id
 * @property int           $databaseId
 * @property string        $orderNumber
 * @property string        $orderKey
 * @property string        $status
 * @property string        $date
 * @property string        $modified
 * @property string        $datePaid
 * @property string        $dateCompleted
 * @property string        $customerNote
 * @property array         $billing
 * @property array         $shipping
 * @property string        $discountTotal
 * @property float         $discountTotalRaw
 * @property string        $discountTax
 * @property string        $discountTaxRaw
 * @property string        $shippingTotal
 * @property float         $shippingTotalRaw
 * @property string        $shippingTax
 * @property string        $shippingTaxRaw
 * @property string        $cartTax
 * @property string        $cartTaxRaw
 * @property string        $subtotal
 * @property float         $subtotalRaw
 * @property string        $total
 * @property float         $totalRaw
 * @property string        $totalTax
 * @property float         $totalTaxRaw
 * @property bool          $isDownloadPermitted
 * @property string        $shippingAddressMapUrl
 * @property bool          $hasBillingAddress
 * @property bool          $hasShippingAddress
 * @property bool          $needsShippingAddress
 * @property bool          $needsPayment
 * @property bool          $needsProcessing
 * @property bool          $hasDownloadableItem
 * @property array         $downloadable_items
 * @property int           $commentCount
 * @property string        $commentStatus
 * @property string        $currency
 * @property string        $paymentMethod
 * @property string        $paymentMethodTitle
 * @property string        $transactionId
 * @property string        $customerIpAddress
 * @property string        $customerUserAgent
 * @property string        $createdVia
 * @property string        $orderKey
 * @property bool          $pricesIncludeTax
 * @property string        $cartHash
 * @property string        $customerNote
 * @property string        $orderVersion
 *
 * @property string        $title
 * @property float         $amount
 * @property string        $reason
 * @property string        $refunded_by_id
 * @property string        $date
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Order extends Model {

	/**
	 * Stores the incoming order data
	 *
	 * @var \WC_Order|\WC_Order_Refund|\WC_Abstract_Order $data
	 */
	protected $data;

	/**
	 * Hold order post type slug
	 *
	 * @var string $post_type
	 */
	protected $post_type;

	/**
	 * Stores the incoming post type object for the post being modeled
	 *
	 * @var null|\WP_Post_Type $post_type_object
	 */
	protected $post_type_object;

	/**
	 * Order constructor.
	 *
	 * @param int|\WC_Data $id - shop_order post-type ID.
	 *
	 * @throws \Exception - Failed to retrieve order data source.
	 */
	public function __construct( $id ) {
		$data = wc_get_order( $id );

		// Check if order is valid.
		if ( ! $data instanceof \WC_Abstract_Order ) {
			throw new \Exception( __( 'Failed to retrieve order data source', 'wp-graphql-woocommerce' ) );
		}

		$this->data                = $data;
		$this->post_type           = $this->get_post_type();
		$this->post_type_object    = ! empty( $this->post_type ) ? get_post_type_object( $this->post_type ) : null;
		$this->current_user        = wp_get_current_user();
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
			'orderNumber',
			'status',
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
		];

		$restricted_cap = $this->get_restricted_cap();

		parent::__construct( $restricted_cap, $allowed_restricted_fields, 1 );
	}

	/**
	 * Get the post type for the order
	 *
	 * @return string
	 */
	public function get_post_type() {
		$object_type = $this->data->get_type();

		$post_type = null;
		switch ( $object_type ) {
			case 'shop_order':
				$post_type = 'shop_order';
				break;
			case 'shop_order_refund':
				$post_type = 'shop_order_refund';
				break;
			default:
				$post_type = apply_filters( 'woographql_order_model_data_post_type', $post_type, $this );
				break;
		}

		return $post_type;
	}

	/**
	 * Forwards function calls to WC_Data sub-class instance.
	 *
	 * @param string $method - function name.
	 * @param array  $args  - function call arguments.
	 *
	 * @throws \BadMethodCallException Method not found on WC data object.
	 *
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		if ( \is_callable( [ $this->data, $method ] ) ) {
			return $this->data->$method( ...$args );
		}

		$class = __CLASS__;
		throw new \BadMethodCallException( "Call to undefined method {$method} on the {$class}" );
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the order
	 *
	 * @return string
	 */
	protected function get_restricted_cap() {
		switch ( $this->data->get_status() ) {
			case 'trash':
				$cap = ! empty( $this->post_type_object )
					? $this->post_type_object->cap->edit_posts
					: 'manage_woocommerce';
				break;
			case 'draft':
			case 'future':
			case 'pending':
				$cap = ! empty( $this->post_type_object )
					? $this->post_type_object->cap->edit_others_posts
					: 'manage_woocommerce';
				break;
			default:
				$cap = '';
				if ( ! $this->owner_matches_current_user() ) {
					$cap = ! empty( $this->post_type_object )
						? $this->post_type_object->cap->edit_posts
						: 'manage_woocommerce';
				}
				break;
		}//end switch

		return $cap;
	}

	/**
	 * Return order types viewable by proven ownership.
	 *
	 * @return array
	 */
	protected function get_viewable_order_types() {
		return apply_filters(
			'woographql_viewable_order_types',
			wc_get_order_types( 'view-orders' ),
		);
	}

	/**
	 * Returns order type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->data->get_type();
	}

	/**
	 * Returns order/refund('s) parent ID
	 *
	 * @return int
	 */
	public function get_parent_id() {
		return absint( $this->data->get_parent_id() );
	}


	/**
	 * Whether or not the customer of the order matches the current user.
	 *
	 * @return bool
	 */
	protected function owner_matches_current_user() {
		/**
		 * Get Order.
		 *
		 * @var \WC_Order $order
		 */
		$order = 'shop_order' !== $this->get_type() ? \wc_get_order( $this->get_parent_id() ) : $this->data;

		if ( ! is_object( $order ) ) {
			return false;
		}

		// Get Customer ID.
		$customer_id = 0;
		if ( in_array( $this->post_type, $this->get_viewable_order_types(), true ) ) {
			$customer_id = $order->get_customer_id();
		}

		// If no customer ID, check if guest order matches current user.
		if ( 0 === $customer_id ) {
			return $this->guest_order_customer_matches_current_user();
		}

		// If no current user or purchasing customer ID, return false.
		if ( empty( $this->current_user->ID ) || empty( $customer_id ) ) {
			return false;
		}

		// If customer ID matches current user, return true.
		return absint( $customer_id ) === absint( $this->current_user->ID ) ? true : false;
	}

	/**
	 * Whether or not the customer of the order who is a guest matches the current user.
	 *
	 * @return bool
	 */
	public function guest_order_customer_matches_current_user() {
		/**
		 * Get Order.
		 *
		 * @var \WC_Order $order
		 */
		$order = 'shop_order' !== $this->get_type() ? \wc_get_order( $this->get_parent_id() ) : $this->data;

		// Get Customer Email.
		if ( in_array( $this->post_type, $this->get_viewable_order_types(), true ) ) {
			$customer_email = $order->get_billing_email();
		}

		// If no customer email, return false.
		$session_customer = \WC()->customer;
		if ( empty( $session_customer->get_billing_email() ) || empty( $customer_email ) ) {
			return false;
		}

		// If customer email matches current user, return true.
		return $customer_email === $session_customer->get_billing_email() ? true : false;
	}

	/**
	 * Determine if the model is private
	 *
	 * @return bool
	 */
	public function is_private() {
		return wc_is_order_status( 'wc-' . $this->data->get_status() ) ? false : true;
	}

	/**
	 * Wrapper function for deleting
	 *
	 * @throws \GraphQL\Error\UserError Not authorized.
	 *
	 * @param boolean $force_delete Should the data be deleted permanently.
	 * @return boolean
	 */
	public function delete( $force_delete = false ) {
		if ( ! current_user_can( ! empty( $this->post_type_object ) ? $this->post_type_object->cap->edit_posts : 'manage_woocommerce' ) ) {
			throw new UserError(
				__(
					'User does not have the capabilities necessary to delete this object.',
					'wp-graphql-woocommerce'
				)
			);
		}

		return $this->data->delete( $force_delete );
	}

	/**
	 * Returns abstract order fields shared by all child order types like Orders and Refunds.
	 *
	 * @return array
	 */
	protected function abstract_order_fields() {
		return [
			'ID'               => function() {
				return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
			},
			'id'               => function() {
				return ! empty( $this->ID ) ? Relay::toGlobalId( 'order', "{$this->ID}" ) : null;
			},
			'databaseId'       => function() {
				return $this->ID;
			},
			'parent_id'        => function() {
				return ! empty( $this->data->get_parent_id() ) ? $this->data->get_parent_id() : null;
			},
			'status'           => function() {
				return ! empty( $this->data->get_status() ) ? $this->data->get_status() : null;
			},
			'currency'         => function() {
				return ! empty( $this->data->get_currency() ) ? $this->data->get_currency() : null;
			},
			'version'          => function() {
				return ! empty( $this->data->get_version() ) ? $this->data->get_version() : null;
			},
			'pricesIncludeTax' => function() {
				return ! empty( $this->data->get_prices_include_tax() ) ? $this->data->get_prices_include_tax() : null;
			},
			'dateCreated'      => function() {
				return ! empty( $this->data->get_date_created() ) ? $this->data->get_date_created() : null;
			},
			'dateModified'     => function() {
				return ! empty( $this->data->get_date_modified() ) ? $this->data->get_date_modified() : null;
			},
			'discountTotal'    => function() {
				$price = ! is_null( $this->data->get_discount_total() ) ? $this->data->get_discount_total() : 0;
				return wc_graphql_price( $price, [ 'currency' => $this->data->get_currency() ] );
			},
			'discountTotalRaw' => function() {
				return ! empty( $this->data->get_discount_total() ) ? $this->data->get_discount_total() : 0;
			},
			'discountTax'      => function() {
				$price = ! is_null( $this->data->get_discount_tax() ) ? $this->data->get_discount_tax() : 0;
				return wc_graphql_price( $price, [ 'currency' => $this->data->get_currency() ] );
			},
			'discountTaxRaw'   => function() {
				return ! empty( $this->data->get_discount_tax() ) ? $this->data->get_discount_tax() : 0;
			},
			'shippingTotal'    => function() {
				$price = ! is_null( $this->data->get_shipping_total() ) ? $this->data->get_shipping_total() : 0;
				return wc_graphql_price( $price, [ 'currency' => $this->data->get_currency() ] );
			},
			'shippingTotalRaw' => function() {
				return ! empty( $this->data->get_shipping_total() ) ? $this->data->get_shipping_total() : 0;
			},
			'shippingTax'      => function() {
				$price = ! is_null( $this->data->get_shipping_tax() ) ? $this->data->get_shipping_tax() : 0;
				return wc_graphql_price( $price, [ 'currency' => $this->data->get_currency() ] );
			},
			'shippingTaxRaw'   => function() {
				return ! empty( $this->data->get_shipping_tax() ) ? $this->data->get_shipping_tax() : 0;
			},
			'cartTax'          => function() {
				$price = ! is_null( $this->data->get_cart_tax() ) ? $this->data->get_cart_tax() : 0;
				return wc_graphql_price( $price, [ 'currency' => $this->data->get_currency() ] );
			},
			'cartTaxRaw'       => function() {
				return ! empty( $this->data->get_cart_tax() ) ? $this->data->get_cart_tax() : 0;
			},
			'total'            => function() {
				return ! is_null( $this->data->get_total() )
					? wc_graphql_price( $this->data->get_total(), [ 'currency' => $this->data->get_currency() ] )
					: null;
			},
			'totalRaw'         => function() {
				return ! empty( $this->data->get_total() ) ? $this->data->get_total() : 0;
			},
			'totalTax'         => function() {
				return ! is_null( $this->data->get_total_tax() )
					? wc_graphql_price( $this->data->get_total_tax(), [ 'currency' => $this->data->get_currency() ] )
					: null;
			},
			'totalTaxRaw'      => function() {
				return ! empty( $this->data->get_total_tax() ) ? $this->data->get_total_tax() : 0;
			},
		];
	}

	/**
	 * Returns order-only fields.
	 *
	 * @return array
	 */
	protected function order_fields() {
		return [
			'date'                  => function() {
				return ! empty( $this->data->get_date_created() ) ? $this->data->get_date_created() : null;
			},
			'modified'              => function() {
				return ! empty( $this->data->get_date_modified() ) ? $this->data->get_date_modified() : null;
			},
			'orderKey'              => function() {
				$order_key = method_exists( $this->data, 'get_order_key' ) ? $this->data->get_order_key() : null;
				return ! empty( $order_key ) ? $order_key : null;
			},
			'paymentMethod'         => function() {
				$payment_method = method_exists( $this->data, 'get_payment_method' ) ? $this->data->get_payment_method() : null;
				return ! empty( $payment_method ) ? $payment_method : null;
			},
			'paymentMethodTitle'    => function() {
				$payment_method_title = method_exists( $this->data, 'get_payment_method_title' ) ? $this->data->get_payment_method_title() : null;
				return ! empty( $payment_method_title ) ? $payment_method_title : null;
			},
			'transactionId'         => function() {
				$transaction_id = method_exists( $this->data, 'get_transaction_id' ) ? $this->data->get_transaction_id() : null;
				return ! empty( $transaction_id ) ? $transaction_id : null;
			},
			'customerIpAddress'     => function() {
				$customer_ip_address = method_exists( $this->data, 'get_customer_ip_address' ) ? $this->data->get_customer_ip_address() : null;
				return ! empty( $customer_ip_address ) ? $customer_ip_address : null;
			},
			'customerUserAgent'     => function() {
				$customer_user_agent = method_exists( $this->data, 'get_customer_user_agent' ) ? $this->data->get_customer_user_agent() : null;
				return ! empty( $customer_user_agent ) ? $customer_user_agent : null;
			},
			'createdVia'            => function() {
				$created_via = method_exists( $this->data, 'get_created_via' ) ? $this->data->get_created_via() : null;
				return ! empty( $created_via ) ? $created_via : null;
			},
			'dateCompleted'         => function() {
				return ! empty( $this->data->get_date_completed() ) ? $this->data->get_date_completed() : null;
			},
			'datePaid'              => function() {
				return ! empty( $this->data->get_date_paid() ) ? $this->data->get_date_paid() : null;
			},
			'subtotal'              => function() {
				return ! empty( $this->data->get_subtotal() )
					? wc_graphql_price( $this->data->get_subtotal(), [ 'currency' => $this->data->get_currency() ] )
					: null;
			},
			'subtotalRaw'           => function() {
				return ! empty( $this->data->get_subtotal() ) ? $this->data->get_subtotal() : 0;
			},
			'orderNumber'           => function() {
				$order_number = method_exists( $this->data, 'get_order_number' ) ? $this->data->get_order_number() : null;
				return ! empty( $order_number ) ? $order_number : null;
			},
			'orderVersion'          => function() {
				return ! empty( $this->data->get_version() ) ? $this->data->get_version() : null;
			},
			'pricesIncludeTax'      => function() {
				return ! is_null( $this->data->get_prices_include_tax() ) ? $this->data->get_prices_include_tax() : null;
			},
			'cartHash'              => function() {
				$cart_hash = method_exists( $this->data, 'get_cart_hash' ) ? $this->data->get_cart_hash() : null;
				return ! empty( $cart_hash ) ? $cart_hash : null;
			},
			'customerNote'          => function() {
				$customer_note = method_exists( $this->data, 'get_customer_note' ) ? $this->data->get_customer_note() : null;
				return ! empty( $customer_note ) ? $customer_note : null;
			},
			'isDownloadPermitted'   => function() {
				if ( ! method_exists( $this->data, 'is_download_permitted' ) ) {
					return null;
				}

				return $this->data->is_download_permitted();
			},
			'billing'               => function() {
				$billing = method_exists( $this->data, 'get_address' ) ? $this->data->get_address( 'billing' ) : null;
				return ! empty( $billing ) ? $billing : null;
			},
			'shipping'              => function() {
				$shipping = method_exists( $this->data, 'get_address' ) ? $this->data->get_address( 'shipping' ) : null;
				return ! empty( $shipping ) ? $shipping : null;
			},
			'shippingAddressMapUrl' => function() {
				$shipping_address_map_url = method_exists( $this->data, 'get_shipping_address_map_url' ) ? $this->data->get_shipping_address_map_url() : null;
				return ! empty( $shipping_address_map_url ) ? $shipping_address_map_url : null;
			},
			'hasBillingAddress'     => function() {
				if ( ! method_exists( $this->data, 'has_billing_address' ) ) {
					return null;
				}

				return $this->data->has_billing_address();
			},
			'hasShippingAddress'    => function() {
				if ( ! method_exists( $this->data, 'has_shipping_address' ) ) {
					return null;
				}

				return $this->data->has_shipping_address();
			},
			'needsShippingAddress'  => function() {
				if ( ! method_exists( $this->data, 'needs_shipping_address' ) ) {
					return null;
				}

				return $this->data->needs_shipping_address();
			},
			'hasDownloadableItem'   => function() {
				if ( ! method_exists( $this->data, 'has_downloadable_item' ) ) {
					return null;
				}

				return $this->data->has_downloadable_item();
			},
			'needsPayment'          => function() {
				if ( ! method_exists( $this->data, 'needs_payment' ) ) {
					return null;
				}

				return $this->data->needs_payment();
			},
			'needsProcessing'       => function() {
				if ( ! method_exists( $this->data, 'needs_processing' ) ) {
					return null;
				}

				return $this->data->needs_processing();
			},
			/**
			 * Connection resolvers fields
			 *
			 * These field resolvers are used in connection resolvers to define WP_Query argument
			 * Note: underscore naming style is used as a quick identifier
			 */
			'customer_id'           => function() {
				$customer_id = method_exists( $this->data, 'get_customer_id' ) ? $this->data->get_customer_id() : null;
				return ! empty( $customer_id ) ? $customer_id : null;
			},
			'downloadable_items'    => function() {
				$downloadable_items = method_exists( $this->data, 'get_downloadable_items' ) ? $this->data->get_downloadable_items() : null;
				return ! empty( $downloadable_items ) ? $downloadable_items : null;
			},
			/**
			 * Defines aliased fields
			 *
			 * These fields are used primarily by WPGraphQL core Node* interfaces
			 * and some fields act as aliases/decorator for existing fields.
			 */
			'commentCount'          => function() {
				remove_filter( 'comments_clauses', [ 'WC_Comments', 'exclude_order_comments' ] );

				$args = [
					'post_id' => $this->ID,
					'approve' => 'approve',
					'fields'  => 'ids',
					'type'    => '',
				];

				$is_allowed_to_edit = null !== $this->post_type_object
					? current_user_can( $this->post_type_object->cap->edit_posts, $this->ID )
					: current_user_can( 'manage_woocommerce' );

				if ( ! $is_allowed_to_edit ) {
					$args += [
						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_key'   => 'is_customer_note',
						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'meta_value' => '1',
					];
				}

				$notes = get_comments( $args );

				add_filter( 'comments_clauses', [ 'WC_Comments', 'exclude_order_comments' ] );

				return is_array( $notes ) ? count( $notes ) : 0;
			},
			'commentStatus'         => function() {
				$is_allowed_to_comment = null !== $this->post_type_object
					? current_user_can( $this->post_type_object->cap->edit_posts, $this->ID )
					: current_user_can( 'manage_woocommerce' );
				return $is_allowed_to_comment ? 'open' : 'closed';
			},
		];
	}

	/**
	 * Returns refund-only fields.
	 *
	 * @return array
	 */
	protected function refund_fields() {
		return [
			'title'          => function() {
				$post_title = method_exists( $this->data, 'get_post_title' ) ? $this->data->get_post_title() : null;
				return ! empty( $post_title ) ? $post_title : null;
			},
			'amount'         => function() {
				$amount = method_exists( $this->data, 'get_amount' ) ? $this->data->get_amount() : null;
				return ! empty( $amount ) ? $amount : null;
			},
			'reason'         => function() {
				$reason = method_exists( $this->data, 'get_reason' ) ? $this->data->get_reason() : null;
				return ! empty( $reason ) ? $reason : null;
			},
			'refunded_by_id' => [
				'callback'   => function() {
					$refunded_by = method_exists( $this->data, 'get_refunded_by' ) ? $this->data->get_refunded_by() : null;
					return ! empty( $refunded_by ) ? $refunded_by : null;
				},
				'capability' => 'list_users',
			],
			'date'           => function() {
				return ! empty( $this->data->get_date_modified() ) ? $this->data->get_date_modified() : null;
			},
		];
	}

	/**
	 * Initializes the Order field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			if ( 'shop_order_refund' === $this->get_type() ) {
				$this->fields = array_merge( $this->refund_fields(), $this->abstract_order_fields() );
			} else {
				$this->fields = array_merge( $this->abstract_order_fields(), $this->order_fields() );
			}
		}//end if
	}
}
