<?php
/**
 * Factory class for the WooCommerce's shipping method data objects.
 *
 * @since v0.10.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Shipping method factory class for testing.
 */
class ShippingZoneFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->dummy                          = Dummy::instance();
		$this->default_generation_definitions = [
			'zone_name'  => 'Test Shipping Zone ' . $this->dummy->number(),
			'zone_order' => 0,
		];
		
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}

		$object = new \WC_Shipping_Zone();

		if ( ! empty( $args['countries'] ) ) {
			foreach ( $args['countries'] as $country ) {
				$object->add_location( $country, 'country' );
			}
		}
		if ( ! empty( $args['states'] ) ) {
			foreach ( $args['states'] as $state ) {
				$object->add_location( $state, 'state' );
			}
		}
		if ( ! empty( $args['postcode'] ) ) {
			foreach ( $args['postcode'] as $postcode ) {
				$object->add_location( $postcode, 'postcode' );
			}
		}

		if ( ! empty( $args['shipping_method'] ) ) {
			$object->add_shipping_method( $args['shipping_method'] );
		}

		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $object, "set_{$key}" ] ) ) {
				$object->{"set_{$key}"}( $value );
			}
		}

		return $object->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Shipping_Zone && 0 !== absint( $object ) ) {
			$object = $this->get_object_by_id( $object );
		}

		if ( ! empty( $fields['countries'] ) ) {
			foreach ( $fields['countries'] as $country ) {
				$object->add_location( $country, 'country' );
			}
		}
		if ( ! empty( $fields['states'] ) ) {
			foreach ( $fields['states'] as $state ) {
				$object->add_location( $state, 'state' );
			}
		}
		if ( ! empty( $fields['postcode'] ) ) {
			foreach ( $fields['postcode'] as $postcode ) {
				$object->add_location( $postcode, 'postcode' );
			}
		}

		if ( ! empty( $fields['shipping_method'] ) ) {
			$object->add_shipping_method( $fields['shipping_method'] );
		}

		foreach ( $fields as $key => $value ) {
			if ( is_callable( [ $object, "set_{$key}" ] ) ) {
				$object->{"set_{$key}"}( $value );
			}
		}

		return $object->save();
	}

	public function get_object_by_id( $id ) {
		if ( class_exists( '\WC_Shipping_Zones' ) ) {
			return \WC_Shipping_Zones::get_zone( $id );
		}

		return false;
	}

	public function getAllZones() {
		if ( class_exists( '\WC_Shipping_Zones' ) ) {
			return \WC_Shipping_Zones::get_zones();
		}

		return false;
	}

	public function reloadShippingMethods() {
		\WC_Cache_Helper::get_transient_version( 'shipping', true );
		WC()->shipping()->load_shipping_methods();
	}

	public function createLegacyFlatRate( $args = [] ) {
		$flat_rate_settings = array_merge(
			[
				'enabled'      => 'yes',
				'title'        => 'Flat rate',
				'availability' => 'all',
				'countries'    => '',
				'tax_status'   => 'taxable',
				'cost'         => '10',
			],
			$args
		);
		update_option( 'woocommerce_flat_rate_settings', $flat_rate_settings );
		update_option( 'woocommerce_flat_rate', [] );
		$this->reloadShippingMethods();

		return 'legacy_flat_rate';
	}

	public function createLegacyFreeShipping( $args = [] ) {
		$free_shipping_settings = array_merge(
			[
				'enabled'      => 'yes',
				'title'        => 'Free shipping',
				'availability' => 'all',
				'countries'    => '',
			],
			$args
		);
		update_option( 'woocommerce_free_shipping_settings', $free_shipping_settings );
		update_option( 'woocommerce_free_shipping', [] );
		$this->reloadShippingMethods();

		return 'legacy_free_shipping';
	}
}
