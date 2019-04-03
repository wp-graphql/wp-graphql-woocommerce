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
	 * @var mixed $data
	 * @access protected
	 */
	protected $data;

	/**
	 * Customer constructor
	 *
	 * @param int $id - User ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->data                = new \WC_Customer( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'userId',
			'name',
		];

		parent::__construct( 'list_users', $allowed_restricted_fields, $id );
	}

	/**
	 * Initializes the Customer field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                    => function() {
					return $this->data->get_id();
				},
				'id'                    => function() {
					return ( ! empty( $this->data ) ) ? Relay::toGlobalId( 'customer', $this->data->get_id() ) : null;
				},
				'customerId'            => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_id() : null;
				},
				'isVatExempt'           => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_is_vat_exempt() : null;
				},
				'hasCalculatedShipping' => function() {
					return ( ! empty( $this->data ) ) ? $this->data->has_calculated_shipping() : null;
				},
				'calculatedShipping'    => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_calculated_shipping() : null;
				},
				'lastOrder'             => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_last_order() : null;
				},
				'orderCount'            => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_order_count() : null;
				},
				'totalSpent'            => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_total_spent() : null;
				},
				'username'              => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_username() : null;
				},
				'email'                 => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_email() : null;
				},
				'firstName'             => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_first_name() : null;
				},
				'lastName'              => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_last_name() : null;
				},
				'displayName'           => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_display_name() : null;
				},
				'role'                  => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_role() : null;
				},
				'date'                  => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_date_created() : null;
				},
				'modified'              => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_date_modified() : null;
				},
				'billing'               => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_billing() : null;
				},
				'shipping'              => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_shipping() : null;
				},
				'isPayingCustomer'      => function() {
					return ( ! empty( $this->data ) ) ? $this->data->get_is_paying_customer() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
