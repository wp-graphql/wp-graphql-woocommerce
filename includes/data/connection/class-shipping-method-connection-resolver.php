<?php
/**
 * ConnectionResolver - Shipping_Method_Connection_Resolver
 *
 * Resolves connections to ShippingMethod
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * Class Shipping_Method_Connection_Resolver
 */
class Shipping_Method_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'shipping_method';
	}

	/**
	 * Confirms if shipping methods should be retrieved.
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates filters for shipping methods.
	 *
	 * @return array|void
	 */
	public function get_query_args() {
		// TODO: Implement get_query_args() method.
		return [];
	}

	/**
	 * Executes query
	 *
	 * @return array|mixed|string[]
	 */
	public function get_query() {
		// TODO: Implement get_query() method.
		$wc_shipping = \WC_Shipping::instance();
		$methods     = $wc_shipping->get_shipping_methods();

		// Get shipping method IDs.
		$methods = array_map(
			static function ( $item ) {
				return $item->id;
			},
			array_values( $methods )
		);

		return $methods;
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array|mixed
	 */
	public function get_ids() {
		return ! empty( $this->query ) ? $this->query : [];
	}

	/**
	 * Validates offset.
	 *
	 * @param mixed $offset  Decoded query cursor.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return is_string( $offset );
	}
}
