<?php
/**
 * Model - Tax_Rate
 *
 * Resolves tax rate object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Model\Model;

/**
 * Class Tax_Rate
 *
 * @property object{
 *  tax_rate_id: int,
 *  tax_rate_class: string,
 *  tax_rate_country: string,
 *  tax_rate_state: string,
 *  tax_rate: string,
 *  tax_rate_name: string,
 *  tax_rate_priority: int,
 *  tax_rate_compound: bool,
 *  tax_rate_shipping: bool,
 *  tax_rate_order: int,
 *  tax_rate_city: string,
 *  tax_rate_postcode: string
 *  } $data
 *
 * @property int    $ID
 * @property string $id
 * @property string $databaseId
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $postcode
 * @property string $rate
 * @property string $name
 * @property string $priority
 * @property bool   $compound
 * @property bool   $shipping
 * @property int    $order
 * @property string $class
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Tax_Rate extends Model {
	/**
	 * Tax_Rate constructor
	 *
	 * @param object{ tax_rate_id: int, tax_rate_class: string, tax_rate_country: string, tax_rate_state: string, tax_rate: string, tax_rate_name: string, tax_rate_priority: int, tax_rate_compound: bool, tax_rate_shipping: bool, tax_rate_order: int, tax_rate_city: string, tax_rate_postcode: string } $rate Tax rate object.
	 */
	public function __construct( $rate ) {
		$this->data                = $rate;
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		];

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$restricted_cap = apply_filters( 'tax_rate_restricted_cap', '' );

		parent::__construct( $restricted_cap, $allowed_restricted_fields, null );
	}

	/**
	 * Determines if the order item should be considered private
	 *
	 * @return bool
	 */
	protected function is_private() {
		return false;
	}

	/**
	 * Initializes the Order field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = [
				'ID'         => function () {
					return $this->data->tax_rate_id;
				},
				'id'         => function () {
					return ! empty( $this->data->tax_rate_id ) ? Relay::toGlobalId( 'tax_rate', (string) $this->data->tax_rate_id ) : null;
				},
				'databaseId' => function () {
					return ! empty( $this->ID ) ? $this->ID : null;
				},
				'country'    => function () {
					return ! empty( $this->data->tax_rate_country ) ? $this->data->tax_rate_country : null;
				},
				'state'      => function () {
					return ! empty( $this->data->tax_rate_state ) ? $this->data->tax_rate_state : null;
				},
				'city'       => function () {
					return ! empty( $this->data->tax_rate_city ) ? $this->data->tax_rate_city : [ '*' ];
				},
				'postcode'   => function () {
					return ! empty( $this->data->tax_rate_postcode ) ? $this->data->tax_rate_postcode : [ '*' ];
				},
				'rate'       => function () {
					return ! empty( $this->data->tax_rate ) ? $this->data->tax_rate : null;
				},
				'name'       => function () {
					return ! empty( $this->data->tax_rate_name ) ? $this->data->tax_rate_name : null;
				},
				'priority'   => function () {
					return ! empty( $this->data->tax_rate_priority ) ? $this->data->tax_rate_priority : null;
				},
				'compound'   => function () {
					return ! empty( $this->data->tax_rate_compound ) ? $this->data->tax_rate_compound : null;
				},
				'shipping'   => function () {
					return ! empty( $this->data->tax_rate_shipping ) ? $this->data->tax_rate_shipping : null;
				},
				'order'      => function () {
					return ! is_null( $this->data->tax_rate_order ) ? absint( $this->data->tax_rate_order ) : null;
				},
				'class'      => function () {
					return ! is_null( $this->data->tax_rate_class ) ? $this->data->tax_rate_class : '';
				},
			];
		}//end if
	}
}
