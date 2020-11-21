<?php
/**
 * ConnectionResolver - Product_Connection_Resolver
 *
 * Resolves connections to Products
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Term;
use WPGraphQL\WooCommerce\Model\Coupon;
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\WooCommerce\Model\Product;
use WPGraphQL\WooCommerce\Model\Product_Variation;

/**
 * Class Product_Connection_Resolver
 */
class Product_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Include CPT Loader connection common functions.
	 */
	use WC_CPT_Loader_Common;

	/**
	 * The name of the post type, or array of post types the connection resolver is resolving for
	 *
	 * @var string|array
	 */
	protected $post_type;

	/**
	 * Holds default catalog visibility tax query.
	 *
	 * @var array
	 */
	public static $default_visibility = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'slug',
		'terms'    => array( 'exclude-from-catalog', 'exclude-from-search' ),
		'operator' => 'NOT IN',
	);

	/**
	 * Refund_Connection_Resolver constructor.
	 *
	 * @param mixed       $source    The object passed down from the previous level in the Resolve tree.
	 * @param array       $args      The input arguments for the query.
	 * @param AppContext  $context   The context of the request.
	 * @param ResolveInfo $info      The resolve info passed down the Resolve tree.
	 */
	public function __construct( $source, $args, $context, $info ) {
		// @codingStandardsIgnoreLine.
		$this->post_type = wc_graphql_ends_with( $info->fieldName, 'ariations' )
			? 'product_variation'
			: 'product';

		/**
		 * Call the parent construct to setup class data
		 */
		parent::__construct( $source, $args, $context, $info );
	}

	/**
	 * Return the name of the loader to be used with the connection resolver
	 *
	 * @return string
	 */
	public function get_loader_name() {
		return 'wc_cpt';
	}

	/**
	 * Given an ID, return the model for the entity or null
	 *
	 * @param integer $id
	 *
	 * @return Product|Product_Variation|null
	 *
	 * @throws \Exception
	 */
	public function get_node_by_id( $id ) {
		$post = get_post( $id );
		if ( empty( $post ) || is_wp_error( $post ) ) {
			return null;
		}

		if ( 'product_variation' === $post->post_type ) {
			return new Product_Variation( $id );
		}

		return new Product( $id );
	}

	/**
	 * Confirms the user has the privileges to query the products
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Creates query arguments array
	 */
	public function get_query_args() {

		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		// Set the $query_args based on various defaults and primary input $args.
		$post_type_obj = get_post_type_object( $this->post_type );
		$query_args    = array(
			'post_type'           => $this->post_type,
			'post_status'         => current_user_can( $post_type_obj->cap->edit_posts ) ? 'any' : 'publish',
			'perm'                => 'readable',
			'no_rows_found'       => true,
			'fields'              => 'ids',
			'posts_per_page'      => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
			'ignore_sticky_posts' => true,
		);

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = array();
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}

		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		/**
		 * Set the graphql_cursor_offset which is used by Config::graphql_wp_query_cursor_pagination_support
		 * to filter the WP_Query to support cursor pagination
		 */
		$cursor_offset                        = $this->get_offset();
		$query_args['graphql_cursor_offset']  = $cursor_offset;
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $this->args;

		// Determine where we're at in the Graph and adjust the query context appropriately.

		if ( isset( $query_args['post__in'] ) && empty( $query_args['post__in'] ) ) {
			$query_args['post__in'] = array( '0' );
		}

		if ( ! current_user_can( $post_type_obj->cap->read_private_posts ) ) {
			if ( empty( $query_args['tax_query'] ) ) {
				$query_args['tax_query'] = array(); // WPCS: slow query ok.
			}

			/**
			 * Filters the default catalog visibility tax query for non-adminstrator requests.
			 *
			 * @param array       $default_visibility  Default catalog visibility tax query.
			 * @param array       $query_args          The args that will be passed to the WP_Query.
			 * @param mixed       $source              The source that's passed down the GraphQL queries.
			 * @param array       $args                The inputArgs on the field.
			 * @param AppContext  $context             The AppContext passed down the GraphQL tree.
			 * @param ResolveInfo $info                The ResolveInfo passed down the GraphQL tree.
			 */
			$catalog_visibility = apply_filters(
				'graphql_product_connection_catalog_visibility',
				self::$default_visibility,
				$query_args,
				$this->source,
				$this->args,
				$this->context,
				$this->info
			);

			if ( ! empty( $catalog_visibility ) ) {
				$query_args['tax_query'][] = $catalog_visibility;
			}
		}

		/**
		 * If the query is a search, the source is not another Post, and the parent input $arg is not
		 * explicitly set in the query, unset the $query_args['post_parent'] so the search
		 * can search all posts, not just top level posts.
		 */
		if ( isset( $query_args['search'] ) && ! isset( $input_fields['parent'] ) ) {
			unset( $query_args['post_parent'] );
		}

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['orderby'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'ASC' : 'DESC';
		}

		/**
		 * Filter the $query_args to allow folks to customize queries programmatically.
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query.
		 * @param mixed       $source     The source that's passed down the GraphQL queries.
		 * @param array       $args       The inputArgs on the field.
		 * @param AppContext  $context    The AppContext passed down the GraphQL tree.
		 * @param ResolveInfo $info       The ResolveInfo passed down the GraphQL tree.
		 */
		$query_args = apply_filters( 'graphql_product_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
	}

	/**
	 * Executes query
	 *
	 * @return \WP_Query
	 */
	public function get_query() {
		$query = new \WP_Query( $this->query_args );

		if ( isset( $query->query_vars['suppress_filters'] ) && true === $query->query_vars['suppress_filters'] ) {
			throw new InvariantViolation( __( 'WP_Query has been modified by a plugin or theme to suppress_filters, which will cause issues with WPGraphQL Execution. If you need to suppress filters for a specific reason within GraphQL, consider registering a custom field to the WPGraphQL Schema with a custom resolver.', 'wp-graphql-woocommerce' ) );
		}

		return $query;
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_ids() {
		return ! empty( $this->query->posts ) ? $this->query->posts : array();
	}

	/**
	 * Returns meta keys to be used for connection ordering.
	 *
	 * @return array
	 */
	public function ordering_meta() {
		return array(
			'_price',
			'_regular_price',
			'_sale_price',
			'_wc_rating_count',
			'_wc_average_rating',
			'_sale_price_dates_from',
			'_sale_price_dates_to',
			'total_sales',
		);
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
		$args = $this->sanitize_common_inputs( $where_args );

		if ( ! empty( $where_args['slug'] ) ) {
			$args['name'] = $where_args['slug'];
		}

		if ( ! empty( $where_args['status'] ) ) {
			$args['post_status'] = $where_args['status'];
		}

		if ( ! empty( $where_args['search'] ) ) {
			$args['s'] = $where_args['search'];
		}

		$tax_query     = array();
		$taxonomy_args = array(
			'type'            => 'product_type',
			'typeIn'          => 'product_type',
			'typeNotIn'       => 'product_type',
			'category'        => 'product_cat',
			'categoryIn'      => 'product_cat',
			'categoryNotIn'   => 'product_cat',
			'categoryId'      => 'product_cat',
			'categoryIdIn'    => 'product_cat',
			'categoryIdNotIn' => 'product_cat',
			'tag'             => 'product_tag',
			'tagIn'           => 'product_tag',
			'tagNotIn'        => 'product_tag',
			'tagId'           => 'product_tag',
			'tagIdIn'         => 'product_tag',
			'tagIdNotIn'      => 'product_tag',
		);

		foreach ( $taxonomy_args as $field => $taxonomy ) {
			if ( ! empty( $where_args[ $field ] ) ) {
				// Set tax query operator.
				switch ( true ) {
					case \wc_graphql_ends_with( $field, 'NotIn' ):
						$operator = 'NOT IN';
						break;
					default:
						$operator = 'IN';
						break;
				}

				// Set tax query config.
				switch ( $field ) {
					case 'type':
					case 'typeIn':
					case 'typeNotIn':
					case 'category':
					case 'categoryIn':
					case 'categoryNotIn':
					case 'tag':
					case 'tagIn':
					case 'tagNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $where_args[ $field ],
							'operator' => $operator,
						);
						break;
					case 'categoryId':
					case 'categoryIdIn':
					case 'categoryIdNotIn':
					case 'tagId':
					case 'tagIdIn':
					case 'tagIdNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $where_args[ $field ],
							'operator' => $operator,
						);
						break;
				}
			}
		}

		if ( 1 < count( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
		}

		// Filter by attribute and term.
		if ( ! empty( $where_args['attribute'] ) && ! empty( $where_args['attributeTerm'] ) ) {
			if ( in_array( $where_args['attribute'], \wc_get_attribute_taxonomy_names(), true ) ) {
				$tax_query[] = array(
					'taxonomy' => $where_args['attribute'],
					'field'    => 'slug',
					'terms'    => $where_args['attributeTerm'],
				);
			}
		}

		if ( empty( $where_args['type'] ) && empty( $where_args['typeIn'] ) && ! empty( $where_args['supportedTypesOnly'] )
			&& true === $where_args['supportedTypesOnly'] ) {
				$supported_types = array_keys( \WP_GraphQL_WooCommerce::get_enabled_product_types() );
				$terms           = ! empty( $where_args['typeNotIn'] )
					? array_diff( $supported_types, $where_args['typeNotIn'] )
					: $supported_types;
			$tax_query[] = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $terms,
			);
		}

		if ( isset( $where_args['featured'] ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			if ( $where_args['featured'] ) {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $product_visibility_term_ids['featured'] ),
				);
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $product_visibility_term_ids['exclude-from-catalog'] ),
					'operator' => 'NOT IN',
				);
			} else {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $product_visibility_term_ids['featured'] ),
					'operator' => 'NOT IN',
				);
			}
		}

		// Handle visibility.
		$post_type_obj = get_post_type_object( $this->post_type );
		if ( ! empty( $where_args['visibility'] ) ) {
			switch ( $where_args['visibility'] ) {
				case 'search':
					$tax_query[] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => array( 'exclude-from-search' ),
						'operator' => 'NOT IN',
					);
					break;
				case 'catalog':
					$tax_query[] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => array( 'exclude-from-catalog' ),
						'operator' => 'NOT IN',
					);
					break;
				case 'visible':
					$tax_query[] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => array( 'exclude-from-catalog', 'exclude-from-search' ),
						'operator' => 'NOT IN',
					);
					break;
				case 'hidden':
					$tax_query[] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => array( 'exclude-from-catalog', 'exclude-from-search' ),
						'operator' => 'AND',
					);
					break;
			}
		}

		// Process "taxonomyFilter".
		if ( ! empty( $where_args['taxonomyFilter'] ) ) {
			foreach ( $where_args['taxonomyFilter'] as $filter ) {
				// Holds sub tax query parameters.
				$sub_tax_query = array();

				// Process parameters.
				foreach ( $filter as $relation => $filter_args ) {
					foreach ( $filter_args as $filter_arg ) {
						$sub_tax_query[] = array(
							'taxonomy' => $filter_arg['taxonomy'],
							'field'    => ! empty( $filter_arg['ids'] ) ? 'term_id' : 'slug',
							'terms'    => ! empty( $filter_arg['ids'] )
								? $filter_arg['ids']
								: $filter_arg['terms'],
							'operator' => ! empty( $filter_arg['operator'] )
								? $filter_arg['operator']
								: 'IN',
						);
					}
				}

				// Set sub tax query relation.
				if ( 1 > count( $sub_tax_query ) ) {
					$sub_tax_query['relation'] = strtoupper( $relation );
				}

				$tax_query[] = $sub_tax_query;
			}
		}

		if ( ! empty( $tax_query ) && 1 > count( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query; // WPCS: slow query ok.
		}

		$meta_query = array();
		if ( ! empty( $where_args['sku'] ) ) {
			$meta_query[] = array(
				'key'     => '_sku',
				'value'   => $where_args['sku'],
				'compare' => 'LIKE',
			);
		}

		if ( ! empty( $where_args['minPrice'] ) || ! empty( $where_args['maxPrice'] ) ) {
			$current_min_price = isset( $where_args['minPrice'] )
				? floatval( $where_args['minPrice'] )
				: 0;
			$current_max_price = isset( $where_args['maxPrice'] )
				? floatval( $where_args['maxPrice'] )
				: PHP_INT_MAX;

			$meta_query[] = apply_filters(
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				'woocommerce_get_min_max_price_meta_query',
				array(
					'key'     => '_price',
					'value'   => array( $current_min_price, $current_max_price ),
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(10,' . wc_get_price_decimals() . ')',
				),
				$args
			);
		}

		if ( isset( $where_args['stockStatus'] ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => $where_args['stockStatus'],
				'compare' => is_array( $where_args['stockStatus'] ) ? 'IN' : '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query; // WPCS: slow query ok.
		}

		if ( ! empty( $where_args['onSale'] ) && is_bool( $where_args['onSale'] ) ) {
			$on_sale_key = $where_args['onSale'] ? 'post__in' : 'post__not_in';
			$on_sale_ids = \wc_get_product_ids_on_sale();

			$on_sale_ids          = empty( $on_sale_ids ) ? array( 0 ) : $on_sale_ids;
			$args[ $on_sale_key ] = $on_sale_ids;
		}

		/**
		 * Filter the input fields
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the WP_Query
		 *
		 * @param array       $args       The mapped query arguments
		 * @param array       $where_args Query "where" args
		 * @param mixed       $source     The query results for a query calling this
		 * @param array       $all_args   All of the arguments for the query (not just the "where" args)
		 * @param AppContext  $context    The AppContext object
		 * @param ResolveInfo $info       The ResolveInfo object
		 * @param mixed|string|array      $post_type  The post type for the query
		 */
		$args = apply_filters(
			'graphql_map_input_fields_to_product_query',
			$args,
			$where_args,
			$this->source,
			$this->args,
			$this->context,
			$this->info,
			$this->post_type
		);

		return $args;
	}

	/**
	 * Wrapper for "WC_Connection_Functions::is_valid_post_offset()"
	 *
	 * @param integer $offset Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return $this->is_valid_post_offset( $offset );
	}
}
