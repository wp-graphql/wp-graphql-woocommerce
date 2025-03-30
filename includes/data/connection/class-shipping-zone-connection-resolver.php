<?php
/**
 * ConnectionResolver - Shipping_Zone_Connection_Resolver
 *
 * Resolves connections to ShippingZone
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\WooCommerce\Model\Shipping_Zone;

/**
 * Class Shipping_Zone_Connection_Resolver
 */
class Shipping_Zone_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'shipping_zone';
	}

	/**
	 * Confirms if shipping methods should be retrieved.
	 *
	 * @return bool
	 */
	public function should_execute() {
		if ( ! \wc_shipping_enabled() ) {
			graphql_debug( __( 'Shipping is disabled.', 'wp-graphql-woocommerce' ) );
			return false;
		}

		if ( ! \wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			graphql_debug( __( 'Permission denied.', 'wp-graphql-woocommerce' ) );
			return false;
		}
		return true;
	}

	/**
	 * Creates filters for shipping zones.
	 *
	 * @return array|void
	 */
	public function get_query_args() {
		return [];
	}

	/**
	 * Executes query
	 *
	 * @return array|mixed|string[]
	 */
	public function get_query() {
		/** @var \WC_Shipping_Zone $rest_of_the_world */
		$rest_of_the_world = \WC_Shipping_Zones::get_zone_by( 'zone_id', 0 );

		$zones = \WC_Shipping_Zones::get_zones();
		array_unshift( $zones, $rest_of_the_world->get_data() );

		if ( ! empty( $this->query_args['filters'] ) && is_array( $this->query_args['filters'] ) ) {
			foreach ( $this->query_args['filters'] as $filter ) {
				$zones = array_filter( $zones, $filter );
			}
		}

		return wp_list_pluck( $zones, 'id' );
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

	/**
	 * Validates shipping zone model.
	 *
	 * @param array $model  Shipping zone model.
	 *
	 * @return bool
	 */
	protected function is_valid_model( $model ) {
		return ! empty( $model ) && $model instanceof Shipping_Zone && 0 !== $model->ID;
	}
}
