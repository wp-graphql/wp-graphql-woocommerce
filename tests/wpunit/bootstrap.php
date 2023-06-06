<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Internal\DataStores\Orders\DataSynchronizer;
use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;
use Automattic\WooCommerce\Internal\Features\FeaturesController;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Remove the "extensions" payload from GraphQL results
 * so that tests can make assertions without worrying about what's in the extensions payload
 */
add_filter(
	'graphql_request_results',
	function( $response ) {
		unset( $response['extensions'] );

		return $response;
	},
	99
);

/**
 * Helper method to drop custom tables if present.
 */
function delete_order_custom_tables() {
	$features_controller = wc_get_container()->get( Featurescontroller::class );
	$features_controller->change_feature_enable( 'custom_order_tables', true );
	$synchronizer = wc_get_container()
		->get( DataSynchronizer::class );
	if ( $synchronizer->check_orders_table_exists() ) {
		$synchronizer->delete_database_tables();
	}
}

/**
 * Enables or disables the custom orders table across WP temporarily.
 *
 * @param boolean $enabled TRUE to enable COT or FALSE to disable.
 * @return void
 */
function toggle_cot( bool $enabled ) {
	$features_controller = wc_get_container()->get( Featurescontroller::class );
	$features_controller->change_feature_enable( 'custom_order_tables', $enabled );

	update_option( CustomOrdersTableController::CUSTOM_ORDERS_TABLE_USAGE_ENABLED_OPTION, wc_bool_to_string( $enabled ) );

	// Confirm things are really correct.
	$wc_data_store = WC_Data_Store::load( 'order' );
	assert( is_a( $wc_data_store->get_current_class_name(), OrdersTableDataStore::class, true ) === $enabled );
}

/**
 * Helper method to create custom tables if not present.
 */
function create_order_custom_table_if_not_exist() {
	$features_controller = wc_get_container()->get( Featurescontroller::class );
	$features_controller->change_feature_enable( 'custom_order_tables', true );

	$synchronizer = wc_get_container()->get( DataSynchronizer::class );
	if ( ! $synchronizer->check_orders_table_exists() ) {
		$synchronizer->create_database_tables();
	}
}

/**
 * Initialize HPOS if tests need to run in HPOS context.
 *
 * @return void
 */
function initialize_hpos() {
	delete_order_custom_tables();
	create_order_custom_table_if_not_exist();
	toggle_cot( true );
}

if ( defined( 'HPOS' ) ) {
	\codecept_debug( 'HPOS activated!!!' );
	initialize_hpos();
}
