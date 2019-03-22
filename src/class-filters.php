<?php
/**
 * Filters
 *
 * Filter callbacks for executing filters on the GraphQL Schema
 *
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce;

/**
 * Class Filters
 */
class Filters {
	/**
	 * Register filters
	 */
	public static function load() {
		/**
		 * Filter Connections query info
		 */
		add_filter(
			'graphql_connection_query_info',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Data\Coupon_Connection_Resolver',
				'query_info_filter',
			),
			10,
			2
		);
		add_filter(
			'graphql_connection_query_info',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Data\Product_Connection_Resolver',
				'query_info_filter',
			),
			10,
			2
		);

		/**
		 * Filter Connection query args
		 */
		add_filter(
			'graphql_post_object_connection_query_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Data\Gallery_Connection_Query_Args',
				'fromProduct',
			),
			10,
			5
		);
	}
}
