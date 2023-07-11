<?php
/**
 * Model - Customer
 *
 * Models WooCommerce post-type data
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WC_Customer;
use WPGraphQL\Model\Model;

/**
 * Class Customer
 *
 * @property \WC_Customer $wc_data
 *
 * @property int $ID
 * @property string $id
 * @property int $databaseId
 * @property bool $isVatExempt
 * @property bool $hasCalculatedShipping
 * @property bool $calculatedShipping
 * @property int $orderCount
 * @property float $totalSpent
 * @property string $username
 * @property string $email
 * @property string $firstName
 * @property string $lastName
 * @property string $displayName
 * @property string $role
 * @property string $date
 * @property string $modified
 * @property array $billing
 * @property array $shipping
 * @property bool $isPayingCustomer
 * @property int $last_order_id
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Customer extends Model {
	/**
	 * Customer constructor
	 *
	 * @param \WC_Customer|int|string $id - User ID.
	 */
	public function __construct( $id = 'session' ) {
		$this->data                = 'session' === $id ? \WC()->customer : new WC_Customer( absint( $id ) );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'customerId',
		];

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$restricted_cap = apply_filters( 'customer_restricted_cap', 'session' === $id ? '' : 'list_users' );

		parent::__construct( $restricted_cap, $allowed_restricted_fields, $this->data->get_id() );
	}

	/**
	 * Forwards function calls to WC_Data sub-class instance.
	 *
	 * @param string $method - function name.
	 * @param array  $args  - function call arguments.
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return $this->data->$method( ...$args );
	}

	/**
	 * Initializes the Customer field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = [
				'ID'                    => function () {
					return ( ! empty( $this->data->get_id() ) ) ? $this->data->get_id() : \WC()->session->get_customer_id();
				},
				'id'                    => function () {
					return ( ! empty( $this->data->get_id() ) )
						? Relay::toGlobalId( 'customer', $this->data->get_id() )
						: 'guest';
				},
				'databaseId'            => function () {
					return ! empty( $this->ID ) ? $this->ID : null;
				},
				'isVatExempt'           => function () {
					return ! is_null( $this->data->get_is_vat_exempt() ) ? $this->data->get_is_vat_exempt() : null;
				},
				'hasCalculatedShipping' => function () {
					return ! is_null( $this->data->has_calculated_shipping() ) ? $this->data->has_calculated_shipping() : null;
				},
				'calculatedShipping'    => function () {
					return ! is_null( $this->data->get_calculated_shipping() ) ? $this->data->get_calculated_shipping() : null;
				},
				'orderCount'            => function () {
					return ! is_null( $this->data->get_order_count() ) ? $this->data->get_order_count() : null;
				},
				'totalSpent'            => function () {
					return ! is_null( $this->data->get_total_spent() ) ? $this->data->get_total_spent() : null;
				},
				'username'              => function () {
					return ( ! empty( $this->data->get_username() ) ) ? $this->data->get_username() : null;
				},
				'email'                 => function () {
					return ( ! empty( $this->data->get_email() ) ) ? $this->data->get_email() : null;
				},
				'firstName'             => function () {
					return ( ! empty( $this->data->get_first_name() ) ) ? $this->data->get_first_name() : null;
				},
				'lastName'              => function () {
					return ( ! empty( $this->data->get_last_name() ) ) ? $this->data->get_last_name() : null;
				},
				'displayName'           => function () {
					return ( ! empty( $this->data->get_display_name() ) ) ? $this->data->get_display_name() : null;
				},
				'role'                  => function () {
					return ( ! empty( $this->data->get_role() ) ) ? $this->data->get_role() : null;
				},
				'date'                  => function () {
					return ( ! empty( $this->data->get_date_created() ) ) ? $this->data->get_date_created() : null;
				},
				'modified'              => function () {
					return ( ! empty( $this->data->get_date_modified() ) ) ? $this->data->get_date_modified() : null;
				},
				'billing'               => function () {
					return ( ! empty( $this->data->get_billing() ) ) ? $this->data->get_billing() : null;
				},
				'shipping'              => function () {
					return ( ! empty( $this->data->get_shipping() ) ) ? $this->data->get_shipping() : null;
				},
				'isPayingCustomer'      => function () {
					return ( ! is_null( $this->data->get_is_paying_customer() ) ) ? $this->data->get_is_paying_customer() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'last_order_id'         => function () {
					return ( ! empty( $this->data->get_last_order() ) ) ? $this->data->get_last_order()->get_id() : null;
				},
			];
		}//end if

		parent::prepare_fields();
	}
}
