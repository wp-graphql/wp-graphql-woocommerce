<?php
/**
 * Adds filters that modify WPGraphQL ACF schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.3.0
 */

namespace WPGraphQL\WooCommerce;

/**
 * Class ACF_Schema_Filters
 */
class ACF_Schema_Filters {

	/**
	 * Register filters
	 */
	public static function add_filters() {
		// Registers WooCommerce CPTs && taxonomies.
		add_filter( 'graphql_acf_get_root_id', array( __CLASS__, 'resolve_crud_root_id' ), 10, 2 );
	}

	/**
	 * Resolve post object ID from CRUD object Model.
	 *
	 * @param integer|null $id    Post object database ID.
	 * @param mixed        $root  Root resolver.
	 *
	 * @return integer|null
	 */
	public static function resolve_crud_root_id( $id, $root ) {
		switch ( true ) {
			case $root instanceof \WPGraphQL\WooCommerce\Model\CRUD_CPT:
				$id = absint( $root->ID );
				break;
		}

		return $id;
	}
}
