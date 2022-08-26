<?php
/**
 * DataLoader - WC_Customer_Loader
 *
 * Loads Customer Model
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

use WPGraphQL\Data\Loader\AbstractDataLoader;
use WPGraphQL\WooCommerce\Model\Customer;

/**
 * Class WC_Customer_Loader
 */
class WC_Customer_Loader extends AbstractDataLoader {
	/**
	 * Processes given array keys and loads Model
	 *
	 * @param array $keys - array of WP User IDs.
	 *
	 * @return array
	 */
	public function loadKeys( array $keys ) {
		if ( empty( $keys ) ) {
			return $keys;
		}
		$all_customers = [];

		/**
		 * Prepare the args for the query. We're provided a specific
		 * set of IDs, so we want to query as efficiently as possible with
		 * as little overhead as possible. We don't want to return post counts,
		 * we don't want to include sticky posts, and we want to limit the query
		 * to the count of the keys provided. The query must also return results
		 * in the same order the keys were provided in.
		 */
		$args = [
			'include'     => $keys,
			'number'      => count( $keys ),
			'orderby'     => 'include',
			'count_total' => false,
			'fields'      => 'ids',
		];

		/**
		 * Query for the users and get the results
		 */
		$query     = new \WP_User_Query( $args );
		$customers = $query->get_results();

		/**
		 * If no users are returned, return an empty array
		 */
		if ( empty( $customers ) || ! is_array( $customers ) ) {
			return [];
		}

		foreach ( $keys as $key ) {
			$all_customers[ $key ] = new Customer( $key );
		}

		return $all_customers;
	}

	/**
	 * Callback for inject the User dataloader with the WC_Customer model.
	 *
	 * @param null  $model  Possible model instance to be loader.
	 * @param mixed $entry  Data source.
	 * @param mixed $key    Data key/ID.
	 * @return \WPGraphQL\Model\Model|null
	 */
	public static function inject_user_loader_models( $model, $entry, $key ) {
		if ( is_null( $model ) && is_a( $entry, \WP_User::class ) ) {
			if ( in_array( 'customer', (array) $entry->roles, true ) ) {
				$model = new Customer( $key );
			}
		}

		return $model;
	}
}
