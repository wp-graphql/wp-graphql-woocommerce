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
 */
class Tax_Rate extends Model {

	/**
	 * Tax_Rate constructor
	 *
	 * @param int $rate - Tax rate object.
	 */
	public function __construct( $rate ) {
		$this->data                = $rate;
		$allowed_restricted_fields = array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		);

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
			$this->fields = array(
				'ID'         => function() {
					return $this->data->tax_rate_id;
				},
				'id'         => function() {
					return ! empty( $this->data->tax_rate_id ) ? Relay::toGlobalId( 'tax_rate', $this->data->tax_rate_id ) : null;
				},
				'databaseId' => function() {
					return $this->ID;
				},
				'country'    => function() {
					return ! empty( $this->data->tax_rate_country ) ? $this->data->tax_rate_country : null;
				},
				'state'      => function() {
					return ! empty( $this->data->tax_rate_state ) ? $this->data->tax_rate_state : null;
				},
				'city'       => function() {
					return ! empty( $this->data->tax_rate_city ) ? $this->data->tax_rate_city : array( '*' );
				},
				'postcode'   => function() {
					return ! empty( $this->data->tax_rate_postcode ) ? $this->data->tax_rate_postcode : array( '*' );
				},
				'rate'       => function() {
					return ! empty( $this->data->tax_rate ) ? $this->data->tax_rate : null;
				},
				'name'       => function() {
					return ! empty( $this->data->tax_rate_name ) ? $this->data->tax_rate_name : null;
				},
				'priority'   => function() {
					return ! empty( $this->data->tax_rate_priority ) ? $this->data->tax_rate_priority : null;
				},
				'compound'   => function() {
					return ! empty( $this->data->tax_rate_compound ) ? $this->data->tax_rate_compound : null;
				},
				'shipping'   => function() {
					return ! empty( $this->data->tax_rate_shipping ) ? $this->data->tax_rate_shipping : null;
				},
				'order'      => function() {
					return ! is_null( $this->data->tax_rate_order ) ? absint( $this->data->tax_rate_order ) : null;
				},
				'class'      => function() {
					return ! is_null( $this->data->tax_rate_class ) ? $this->data->tax_rate_class : '';
				},
			);
		}
	}
}
