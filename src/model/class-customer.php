<?php
/**
 * Model - Customer
 *
 * Models WooCommerce post-type data
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Model;

/**
 * Class Customer
 */
class Customer extends Model {
	/**
	 * Stores the instance of WC customer data-store object
	 *
	 * @var mixed $customer
	 * @access protected
	 */
	protected $customer;

	/**
	 * Customer constructor
	 *
	 * @param int $id - User ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->customer            = new \WC_Customer( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'userId',
			'name',
		];

		parent::__construct( 'CustomerObject', $this->customer, 'list_users', $allowed_restricted_fields, $id );
		$this->init();
	}

	/**
	 * Initializes the Customer field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( 'private' === $this->get_visibility() || is_null( $this->customer ) ) {
			return null;
		}

		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                    => function() {
					return $this->customer->get_id();
				},
				'id'                    => function() {
					return ( ! empty( $this->customer ) ) ? Relay::toGlobalId( 'customer', $this->customer->get_id() ) : null;
				},
				'customerId'            => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_id() : null;
				},
				'isVatExempt'           => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_is_vat_exempt() : null;
				},
				'hasCalculatedShipping' => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->has_calculated_shipping() : null;
				},
				'calculatedShipping'    => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_calculated_shipping() : null;
				},
				'lastOrder'             => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_last_order() : null;
				},
				'orderCount'            => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_order_count() : null;
				},
				'totalSpent'            => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_total_spent() : null;
				},
				'username'              => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_username() : null;
				},
				'email'                 => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_email() : null;
				},
				'firstName'             => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_first_name() : null;
				},
				'lastName'              => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_last_name() : null;
				},
				'displayName'           => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_display_name() : null;
				},
				'role'                  => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_role() : null;
				},
				'date'                  => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_date_created() : null;
				},
				'modified'              => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_date_modified() : null;
				},
				'billing'               => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_billing() : null;
				},
				'shipping'              => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_shipping() : null;
				},
				'isPayingCustomer'      => function() {
					return ( ! empty( $this->customer ) ) ? $this->customer->get_is_paying_customer() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
