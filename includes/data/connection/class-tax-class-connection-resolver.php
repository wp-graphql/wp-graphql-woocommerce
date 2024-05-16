<?php
/**
 * ConnectionResolver - Tax_Class_Connection_Resolver
 *
 * Resolves connections to TaxClass
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since TBD
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
		$tax_classes = array();

		// Add standard class.
		$tax_classes[] = array(
			'slug' => 'standard',
			'name' => __( 'Standard rate', 'woocommerce' ),
		);

		$classes = \WC_Tax::get_tax_classes();

		foreach ( $classes as $class ) {
			$tax_classes[] = array(
				'slug' => sanitize_title( $class ),
				'name' => $class,
			);
		}

        // Cache cart items for later.
		foreach ( $tax_classes as $tax_class ) {
			$this->loader->prime( $tax_class['slug'], $tax_class );
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
