<?php
/**
 * ConnectionResolver - Tax_Class_Connection_Resolver
 *
 * Resolves connections to TaxClass
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * Class Tax_Class_Connection_Resolver
 */
class Tax_Class_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'tax_class';
	}

	/**
	 * Confirms if tax classes should be retrieved.
	 *
	 * @return bool
	 */
	public function should_execute() {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			graphql_debug(
				__( 'User does not have permission to view tax classes.', 'wp-graphql-woocommerce' )
			);
			return false;
		}
		return true;
	}

	/**
	 * Creates filters for tax classes.
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
		$tax_classes = [];

		// Add standard class.
		$tax_classes[] = [
			'slug' => 'standard',
			'name' => __( 'Standard rate', 'wp-graphql-woocommerce' ),
		];

		$classes = \WC_Tax::get_tax_classes();

		foreach ( $classes as $class ) {
			$tax_classes[] = [
				'slug' => sanitize_title( $class ),
				'name' => $class,
			];
		}

		// Cache cart items for later.
		foreach ( $tax_classes as $tax_class ) {
			$this->get_loader()->prime( $tax_class['slug'], $tax_class );
		}

		return wp_list_pluck( $tax_classes, 'slug' );
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
	 * Validates tax class model.
	 *
	 * @param array $model  Tax class model.
	 *
	 * @return bool
	 */
	protected function is_valid_model( $model ) {
		return is_array( $model ) && ! empty( $model['name'] ) && ! empty( $model['slug'] );
	}
}
