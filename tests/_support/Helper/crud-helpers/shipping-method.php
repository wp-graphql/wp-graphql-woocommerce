<?php

use GraphQLRelay\Relay;

class ShippingMethodHelper extends WCG_Helper {
	public function __construct() {
		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'shipping_method', $id );
	}

	public function create_legacy_flat_rate_instance( $args = array() ) {
		$flat_rate_settings = array_merge(
			array(
				'enabled'      => 'yes',
				'title'        => 'Flat rate',
				'availability' => 'all',
				'countries'    => '',
				'tax_status'   => 'taxable',
				'cost'         => '10',
			),
			$args
		);
		update_option( 'woocommerce_flat_rate_settings', $flat_rate_settings );
		update_option( 'woocommerce_flat_rate', array() );
		WC_Cache_Helper::get_transient_version( 'shipping', true );
		WC()->shipping()->load_shipping_methods();

		return 'legacy_flat_rate';
	}

	public function create_legacy_free_shipping_instance( $args = array() ) {
		$free_shipping_settings = array_merge(
			array(
				'enabled'      => 'yes',
				'title'        => 'Free shipping',
				'availability' => 'all',
				'countries'    => '',
			),
			$args
		);
		update_option( 'woocommerce_free_shipping_settings', $free_shipping_settings );
		update_option( 'woocommerce_free_shipping', array() );
		WC_Cache_Helper::get_transient_version( 'shipping', true );
		WC()->shipping()->load_shipping_methods();

		return 'legacy_free_shipping';
	}

	public function print_query( $id ) {
		$wc_shipping = \WC_Shipping::instance();
		$methods     = $wc_shipping->get_shipping_methods();
		if ( empty( $methods[ $id ] ) ) {
			return null;
		}

		$method = $methods[ $id ];
		return array(
			'id'          => $this->to_relay_id( $id ),
			'databaseId'  => $id,
			'title'       => $method->method_title,
			'description' => $method->method_description,
		);
	}

	public function print_nodes( $ids = 0, $processors = array() ) {
		return array();
	}
}
