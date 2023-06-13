<?php
/**
 * ConnectionResolver - Downloadable_Item_Connection_Resolver
 *
 * Resolves connections to DownloadableItem
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.4.0
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\WooCommerce\Data\Loader\WC_Db_Loader;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Model\Customer;

/**
 * Class Downloadable_Item_Connection_Resolver
 *
 * @property WC_Db_Loader $loader
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 */
class Downloadable_Item_Connection_Resolver extends AbstractConnectionResolver {

	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'downloadable_item';
	}

	/**
	 * Confirms if downloadable items should be retrieved.
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates downloadable item filters.
	 *
	 * @return array
	 */
	public function get_query_args() {
		$query_args = [ 'filters' => [] ];
		if ( ! empty( $this->args['where'] ) ) {
			$where_args = $this->args['where'];
			if ( isset( $where_args['active'] ) ) {
				$active = $where_args['active'];

				$query_args['filters'][] = function( $downloadable_item ) use ( $active ) {
					$is_expired          = isset( $downloadable_item['access_expires'] )
						? time() > $downloadable_item['access_expires']->getTimestamp()
						: false;
					$downloads_remaining = ( 'integer' === gettype( $downloadable_item['downloads_remaining'] ) )
						? 0 < $downloadable_item['downloads_remaining']
						: true;

					return $active ? ( ! $is_expired && $downloads_remaining ) : ( $is_expired || ! $downloads_remaining );
				};
			}

			if ( isset( $where_args['expired'] ) ) {
				$expired = $where_args['expired'];

				$query_args['filters'][] = function( $downloadable_item ) use ( $expired ) {
					$is_expired = isset( $downloadable_item['access_expires'] )
						? time() < $downloadable_item['access_expires']->getTimestamp()
						: false;

					return $expired === $is_expired;
				};
			}

			if ( isset( $where_args['hasDownloadsRemaining'] ) ) {
				$has_downloads_remaining = $where_args['hasDownloadsRemaining'];

				$query_args['filters'][] = function( $downloadable_item ) use ( $has_downloads_remaining ) {
					$downloads_remaining = ( 'integer' === gettype( $downloadable_item['downloads_remaining'] ) )
						? 0 < $downloadable_item['downloads_remaining']
						: true;

					return $has_downloads_remaining === $downloads_remaining;
				};
			}
		}//end if

		/**
		 * Filter the $query_args to allow folks to customize queries programmatically.
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query.
		 * @param mixed       $source     The source that's passed down the GraphQL queries.
		 * @param array       $args       The inputArgs on the field.
		 * @param AppContext  $context    The AppContext passed down the GraphQL tree.
		 * @param ResolveInfo $info       The ResolveInfo passed down the GraphQL tree.
		 */
		$query_args = apply_filters( 'graphql_downloadable_item_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return array
	 */
	public function get_query() {
		$items = 0;
		if ( is_a( $this->source, Customer::class ) ) {
			$items = wc_get_customer_available_downloads( $this->source->ID );
		} else {
			$items = $this->source->downloadable_items;
		}

		if ( empty( $items ) ) {
			return [];
		}

		if ( ! empty( $this->query_args['filters'] ) && is_array( $this->query_args['filters'] ) ) {
			foreach ( $this->query_args['filters'] as $filter ) {
				$items = array_filter( $items, $filter );
			}
		}

		// Cache items for later.
		foreach ( $items as $item ) {
			$this->loader->prime( $item['download_id'], $item );
		}

		return array_column( $items, 'download_id' );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids_from_query() {
		$ids = ! empty( $this->query ) ? $this->query : [];

		return $ids;
	}

	/**
	 * Validates offset.
	 *
	 * @param integer $offset Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return 'string' === gettype( $offset );
	}

	/**
	 * Validates Model.
	 *
	 * If model isn't a class with a `fields` member, this function with have be overridden in
	 * the Connection class.
	 *
	 * @param array $model Downloadable item model.
	 *
	 * @return bool
	 */
	protected function is_valid_model( $model ) {
		return ! empty( $model ) && ! empty( $model['download_id'] );
	}
}
