<?php
/**
 * ConnectionResolver - Tax_Rate_Connection_Resolver
 *
 * Resolves connections to Tax Rates
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class Tax_Rate_Connection_Resolver
 */
class Tax_Rate_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Confirms the uses has the privileges to query Tax Rates
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates query arguments array
	 *
	 * @return array
	 */
	public function get_query_args() {
		$query_args = array();

		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = [];
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}

		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		/**
		 * Set posts_per_page the highest value of $first and $last, with a (filterable) max of 100
		 */
		$query_args['items_per_page'] = min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1;

		/**
		 * Set the graphql_cursor_offset which is used by Config::graphql_wp_query_cursor_pagination_support
		 * to filter the WP_Query to support cursor pagination
		 */
		$cursor_offset                        = $this->get_offset();
		$query_args['graphql_cursor_offset']  = $cursor_offset;
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['orderby'] ) ) {
			$query_args['orderby'] = 'tax_rate_order';
		}
		if ( empty( $query_args['order'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'ASC' : 'DESC';
		}

		/**
		 * Filter the $query args to allow folks to customize queries programmatically
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query
		 * @param mixed       $source     The source that's passed down the GraphQL queries
		 * @param array       $args       The inputArgs on the field
		 * @param AppContext  $context    The AppContext passed down the GraphQL tree
		 * @param ResolveInfo $info       The ResolveInfo passed down the GraphQL tree
		 */
		$query_args = apply_filters( 'graphql_tax_rate_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return array
	 */
	public function get_query() {
		global $wpdb;

		if ( ! empty( $this->query_args['column'] ) ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT tax_rate_id
					FROM {$wpdb->prefix}woocommerce_tax_rates
					WHERE %1s = %s
					ORDER BY %s %s",
					$this->query_args['column'],
					$this->query_args['column_value'],
					$this->query_args['orderby'],
					$this->query_args['order']
				)
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT tax_rate_id
					FROM {$wpdb->prefix}woocommerce_tax_rates
					ORDER BY %s %s",
					$this->query_args['orderby'],
					$this->query_args['order']
				)
			);
		}

		$results = array_map(
			function( $rate ) {
				return $rate->tax_rate_id;
			},
			(array) $results
		);
		return $results;
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		return ! empty( $this->query ) ? $this->query : [];
	}

	/**
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to WP_Query
	 * friendly keys. There's probably a cleaner/more dynamic way to approach this, but
	 * this was quick. I'd be down to explore more dynamic ways to map this, but for
	 * now this gets the job done.
	 *
	 * @param array $where_args - arguments being used to filter query.
	 *
	 * @return array
	 */
	public function sanitize_input_fields( array $where_args ) {
		$args = array();
		if ( ! empty( $where_args['orderby'] ) ) {
			if ( ! empty( $where_args['orderby']['field'] ) ) {
				$orderby_possibles = array(
					'id'    => 'tax_rate_id',
					'order' => 'tax_rate_order',
				);
				$args['orderby']   = $orderby_possibles[ $where_args['orderby']['field'] ];
			}

			if ( ! empty( $where_args['orderby']['order'] ) ) {
				$args['order'] = $where_args['orderby']['order'];
			}
		}

		if ( ! empty( $where_args['class'] ) ) {
			$args['column']       = 'tax_rate_class';
			$args['column_value'] = 'standard' !== $where_args['class'] ? $where_args['class'] : '';
		}

		return $args;
	}
}
