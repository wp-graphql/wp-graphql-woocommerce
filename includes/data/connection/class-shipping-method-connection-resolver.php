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
use WPGraphQL\WooCommerce\Model\Shipping_Method;
use WPGraphQL\WooCommerce\Model\Shipping_Zone;

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
		if ( ! wc_rest_check_manager_permissions( 'shipping_methods', 'read' ) ) {
			graphql_debug( __( 'Permission denied.', 'wp-graphql-woocommerce' ) );
			return false;
		}
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
	 * @return int[]
	 */
	public function get_query() {
		if ( $this->source instanceof Shipping_Zone ) {
			$methods = $this->source->methods;
		} else {
			$wc_shipping = \WC_Shipping::instance();
			$methods     = $wc_shipping->get_shipping_methods();
		}

		foreach ( $methods as $method ) {
			$this->get_loader()->prime( $method->id, new Shipping_Method( $method ) );
		}

		// Get shipping method IDs.
		$methods = wp_list_pluck( array_values( $methods ), 'id' );

		return $methods;
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids_from_query() {
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
