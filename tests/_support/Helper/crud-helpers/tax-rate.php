<?php

use GraphQLRelay\Relay;
use WPGraphQL\Type\WPEnumType;

class TaxRateHelper extends WCG_Helper {
	private $index;

	protected function __construct() {
		$this->index = 0;

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'tax_rate', $id );
	}

	public function get_index() {
		return $this->index++;
	}

	public function create( $args = array() ) {
		$rate_args = array();
		$fields = array(
			'country'  => 'tax_rate_country',
			'state'    => 'tax_rate_state',
			'rate'     => 'tax_rate',
			'name'     => 'tax_rate_name',
			'priority' => 'tax_rate_priority',
			'compound' => 'tax_rate_compound',
			'shipping' => 'tax_rate_shipping',
			'order'    => 'tax_rate_order',
			'class'    => 'tax_rate_class',
		);
		foreach( $args as $key => $value ) {
			if ( in_array( $key, array_keys( $fields ) ) ) {
				$rate_args[ $fields[ $key ] ] = $value;
			}
		}

		$rate_args = array_merge(
			array(
				'tax_rate_country'  => 'US',
				'tax_rate_state'    => '*',
				'tax_rate'          => 20.0000,
				'tax_rate_name'     => "VAT",
				'tax_rate_priority' => 1,
				'tax_rate_compound' => 1,
				'tax_rate_shipping' => 1,
				'tax_rate_order'    => $this->get_index(),
			),
			$rate_args
		);

		$id = WC_Tax::_insert_tax_rate( $rate_args );
		empty( $args['postcode'] ) || WC_Tax::_update_tax_rate_postcodes( $id, wc_clean( $args['postcode'] ) );
		empty( $args['city'] ) || WC_Tax::_update_tax_rate_cities( $id, wc_clean( $args['city'] ) );

		return $id;
	}

	public function get_rate_object( $id ) {
		global $wpdb;
		$rate = WC_Tax::_get_tax_rate( $id, OBJECT );
		if ( \is_wp_error( $rate ) || empty( $rate ) ) {
			return null;
		}

		// Get locales from a tax rate.
		$locales = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT location_code, location_type
				FROM {$wpdb->prefix}woocommerce_tax_rate_locations
				WHERE tax_rate_id = %d",
				$id
			)
		);

		foreach ( $locales as $locale ) {
			if ( empty( $rate->{'tax_rate_' . $locale->location_type} ) ) {
				$rate->{'tax_rate_' . $locale->location_type} = array();
			}
			$rate->{'tax_rate_' . $locale->location_type}[] = $locale->location_code;
		}

		return $rate;
	}

	public function print_query( $id ) {
		$rate = $this->get_rate_object( $id );

		return $rate
			? array(
				'id'         => $this->to_relay_id( $rate->tax_rate_id ),
				'databaseId' => absint( $rate->tax_rate_id ),
				'country'    => ! empty( $rate->tax_rate_country ) ? $rate->tax_rate_country : null,
				'state'      => ! empty( $rate->tax_rate_state ) ? $rate->tax_rate_state : null,
				'postcode'   => ! empty( $rate->tax_rate_postcode ) ? $rate->tax_rate_postcode : array( "*" ),
				'city'       => ! empty( $rate->tax_rate_city ) ? $rate->tax_rate_city : array( "*" ),
				'rate'       => ! empty( $rate->tax_rate ) ? $rate->tax_rate : null,
				'name'       => ! empty( $rate->tax_rate_name ) ? $rate->tax_rate_name : null,
				'priority'   => absint( $rate->tax_rate_priority ),
				'compound'   => (bool) $rate->tax_rate_compound,
				'shipping'   => (bool) $rate->tax_rate_shipping,
				'order'      => absint( $rate->tax_rate_order ),
				'class'      => ! empty( $rate->tax_rate_class )
					? WPEnumType::get_safe_name( $rate->tax_rate_class )
					: 'STANDARD',
			)
			: null;
	}

	public function print_nodes( $ids = 0, $processors = array() ) {
		return array();
	}
}
