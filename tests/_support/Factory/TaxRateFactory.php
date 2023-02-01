<?php
/**
 * Factory class for the WooCommerce's tax rate data objects.
 *
 * @since v0.10.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Tax Rate factory class for testing.
 */
class TaxRateFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'zone_name' => '',
		];
		$this->dummy                          = Dummy::instance();
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}

		$rate_args = [];
		$fields    = [
			'country'  => 'tax_rate_country',
			'state'    => 'tax_rate_state',
			'rate'     => 'tax_rate',
			'name'     => 'tax_rate_name',
			'priority' => 'tax_rate_priority',
			'compound' => 'tax_rate_compound',
			'shipping' => 'tax_rate_shipping',
			'order'    => 'tax_rate_order',
			'class'    => 'tax_rate_class',
		];

		foreach ( $args as $key => $value ) {
			if ( in_array( $key, array_keys( $fields ), true ) ) {
				$rate_args[ $fields[ $key ] ] = $value;
			}
		}

		$rate_args = array_merge(
			[
				'tax_rate_country'  => 'US',
				'tax_rate_state'    => '*',
				'tax_rate'          => 20.0000,
				'tax_rate_name'     => 'VAT',
				'tax_rate_priority' => 1,
				'tax_rate_compound' => 1,
				'tax_rate_shipping' => 1,
				'tax_rate_order'    => 0,
			],
			$rate_args
		);

		$id = \WC_Tax::_insert_tax_rate( $rate_args );
		empty( $args['postcode'] ) || \WC_Tax::_update_tax_rate_postcodes( $id, wc_clean( $args['postcode'] ) );
		empty( $args['city'] ) || \WC_Tax::_update_tax_rate_cities( $id, wc_clean( $args['city'] ) );

		return $id;
	}

	public function update_object( $object, $fields ) {
		\WC_Tax::_update_tax_rate( $object['tax_rate_id'], $object );
	}

	public function get_object_by_id( $id ) {
		global $wpdb;
		$rate = \WC_Tax::_get_tax_rate( $id, OBJECT );
		if ( \is_wp_error( $rate ) || empty( $rate ) ) {
			return null;
		}

		// Get locales from a tax rate.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
				$rate->{'tax_rate_' . $locale->location_type} = [];
			}
			$rate->{'tax_rate_' . $locale->location_type}[] = $locale->location_code;
		}

		return $rate;
	}
}
