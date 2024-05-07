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

use Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Utils\Utils;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class Product_Connection_Resolver
 *
 * @deprecated v0.10.0
 *
 * @property \WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader $loader
 */
class Product_Connection_Resolver extends AbstractConnectionResolver {
	/**
	 * Include CPT Loader connection common functions.
	 */
	use WC_CPT_Loader_Common;

	/**
	 * The name of the post type, or array of post types the connection resolver is resolving for
	 *
	 * @var string[]
	 */
	protected $post_type;

	/**
	 * The instance of the class that helps filtering with the product attributes lookup table.
	 *
	 * @var \Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer
	 */
	private $filterer; // @phpstan-ignore-line

	/**
	 * Refund_Connection_Resolver constructor.
	 *
	 * @param mixed                                $source    The object passed down from the previous level in the Resolve tree.
	 * @param array                                $args      The input arguments for the query.
	 * @param \WPGraphQL\AppContext                $context   The context of the request.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info      The resolve info passed down the Resolve tree.
	 */
	public function __construct( $source, $args, $context, $info ) {
		// @codingStandardsIgnoreLine.
		$this->post_type = ['product'];

		$this->filterer = wc_get_container()->get( Filterer::class ); // @phpstan-ignore-line

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
		return 'wc_post';
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
	 * {@inheritDoc}
	 */
	public function get_query_args() {
		/**
		 * Prepare for later use
		 */
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		$query_args = [];
		/**
		 * Ignore sticky posts by default
		 */
		$query_args['ignore_sticky_posts'] = true;

		/**
		 * Set post_type
		 */
		$query_args['post_type'] = $this->post_type;

		/**
		 * Set the wc_query to product_query
		 */
		$query_args['wc_query'] = 'product_query';

		/**
		 * Set the post_status
		 */
		$query_args['post_status'] = [ 'draft', 'pending', 'private', 'publish' ];
		$query_args['perm']        = 'readable';

		/**
		 * Set posts_per_page the highest value of $first and $last, with a (filterable) max of 100
		 */
		$query_args['posts_per_page'] = $this->one_to_one ? 1 : min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1;

		/**
		 * Set the graphql cursor args.
		 */
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';
		$query_args['graphql_after_cursor']   = $this->get_after_offset();
		$query_args['graphql_before_cursor']  = $this->get_before_offset();

		/**
		 * If the cursor offsets not empty,
		 * ignore sticky posts on the query
		 * and don't count the total number of posts
		 */
		if ( ! empty( $this->get_after_offset() ) || ! empty( $this->get_before_offset() ) ) {
			$query_args['ignore_sticky_posts'] = true;
			$query_args['no_found_rows']       = true;
		}

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $this->args;

		/**
		 * Collect the input_fields and sanitize them to prepare them for sending to the WP_Query
		 */
		$input_fields = [];
		if ( ! empty( $this->args['where'] ) ) {
			$input_fields = $this->sanitize_input_fields( $this->args['where'] );
		}

		/**
		 * Merge the input_fields with the default query_args
		 */
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}


		/**
		 * If the query contains search default the results to
		 */
		if ( isset( $query_args['search'] ) && ! empty( $query_args['search'] ) ) {
			/**
			 * Don't order search results by title (causes funky issues with cursors)
			 */
			$query_args['search_orderby_title'] = false;
			$query_args = array_merge( $query_args, \WC()->query->get_catalog_ordering_args( 'relevance', isset( $last ) ? 'ASC' : 'DESC' ) );
		}

		if ( empty( $query_args['orderby'] ) ) {
			$query_args = array_merge( $query_args, \WC()->query->get_catalog_ordering_args( 'menu_order', isset( $last ) ? 'ASC' : 'DESC' ) );
		}

		/**
		 * NOTE: Only IDs should be queried here as the Deferred resolution will handle
		 * fetching the full objects, either from cache of from a follow-up query to the DB
		 */
		$query_args['fields'] = 'ids';

		/**
		 * Filter the $query args to allow folks to customize queries programmatically
		 *
		 * @param array                                $query_args The args that will be passed to the WP_Query
		 * @param mixed                                $source     The source that's passed down the GraphQL queries
		 * @param array                                $args       The inputArgs on the field
		 * @param \WPGraphQL\AppContext                $context The AppContext passed down the GraphQL tree
		 * @param \GraphQL\Type\Definition\ResolveInfo $info The ResolveInfo passed down the GraphQL tree
		 */
		return apply_filters( 'graphql_product_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return \WP_Query
	 */
	public function get_query() {
		// Run query and add product query filters.
		$wp_query             = new \WP_Query();
		$wp_query->query_vars = wp_parse_args( $this->query_args );
		add_filter( 'posts_clauses', [ $this, 'product_query_post_clauses' ], 10, 2 );

		return $wp_query;
	}

	/**
	 * Filter the product query and apply WC's custom clauses.
	 *
	 * @param array     $args      The query clauses.
	 * @param \WP_Query $wp_query  The WP_Query object.
	 *
	 * @return array
	 */
	public function product_query_post_clauses( $args, $wp_query ) {
		if ( 'product_query' !== $wp_query->get( 'wc_query' ) ) {
			return $args;
		}

		$args = $this->price_filter_post_clauses( $args, $wp_query );
		$args = $this->filterer->filter_by_attribute_post_clauses( $args, $wp_query, [] ); // @phpstan-ignore-line

		return $args;
	}

	/**
	 * Custom query used to filter products by price.
	 *
	 * @param array     $args      SQL clauses                                         $args      Query args.
	 * @param \WP_Query $wp_query  WP_Query object.
	 *
	 * @return array
	 */
	public function price_filter_post_clauses( $args, $wp_query ) {
		global $wpdb;

		$min_price = $wp_query->get( 'min_price' );
		$max_price = $wp_query->get( 'max_price' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$current_min_price = $min_price ?: 0;
		$current_max_price = $max_price ?: PHP_INT_MAX;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		/**
		 * Adjust if the store taxes are not displayed how they are stored.
		 * Kicks in when prices excluding tax are displayed including tax.
		 */
		if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
			$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$tax_rates = \WC_Tax::get_rates( $tax_class );

			if ( $tax_rates ) {
				$current_min_price -= \WC_Tax::get_tax_total( \WC_Tax::calc_inclusive_tax( $current_min_price, $tax_rates ) );
				$current_max_price -= \WC_Tax::get_tax_total( \WC_Tax::calc_inclusive_tax( $current_max_price, $tax_rates ) );
			}
		}

		$args['join']  .= ! strstr( $args['join'], 'wc_product_meta_lookup' )
			? " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id "
			: '';
		$args['where'] .= $wpdb->prepare(
			' AND NOT (%f<wc_product_meta_lookup.min_price OR %f>wc_product_meta_lookup.max_price ) ',
			$current_max_price,
			$current_min_price
		);
		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ids_from_query() {
		$ids = $this->query->get_posts();

		// If we're going backwards, we need to reverse the array.
		if ( ! empty( $this->args['last'] ) ) {
			$ids = array_reverse( $ids );
		}

		return $ids;
	}

	/**
	 * Returns meta keys to be used for connection ordering.
	 *
	 * @param bool $is_numeric  Return numeric meta keys. Defaults to "true".
	 * 
	 * @return array
	 */
	public function ordering_meta( $is_numeric = true ) {
		if ( ! $is_numeric ) {
			return apply_filters(
				'woographql_product_connection_orderby_meta_keys',
				[]
			);
		}

		return apply_filters(
			'woographql_product_connection_orderby_numeric_meta_keys',
			[
				'_price',
				'_regular_price',
				'_sale_price',
				'_wc_rating_count',
				'_wc_average_rating',
				'_sale_price_dates_from',
				'_sale_price_dates_to',
				'total_sales',
			]
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
		$query_args = Utils::map_input(
			$where_args,
			[
				'slugIn'      => 'post_name__in',
				'minPrice'    => 'min_price',
				'maxPrice'    => 'max_price',
				'stockStatus' => 'stock_status',
				'status'      => 'post_status',
				'include'     => 'post__in',
				'exclude'     => 'post__not_in',
				'parent'      => 'post_parent',
				'parentIn'    => 'post_parent__in',
				'parentNotIn' => 'post_parent__not_in',
				'search'      => 'search',
			]
		);

		if ( ! empty( $where_args['orderby'] ) ) {
			$default_order = isset( $this->args['last'] ) ? 'ASC' : 'DESC';
			$orderby_input = current( $where_args['orderby'] );

			$orderby    = $orderby_input['field'];
			$order      = ! empty( $orderby_input['order'] ) ? $orderby_input['order'] : $default_order;
			$query_args = array_merge( $query_args, \WC()->query->get_catalog_ordering_args( $orderby, $order ) );
		}

		if ( isset( $where_args['includeVariations'] ) && $where_args['includeVariations'] ) {
			$query_args['post_type'] = [ 'product', 'product_variation' ];
		}

		$tax_query     = [];
		$taxonomy_args = [
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
		];

		foreach ( $taxonomy_args as $field => $taxonomy ) {
			if ( ! empty( $where_args[ $field ] ) ) {
				// Set tax query operator.
				switch ( true ) {
					case str_ends_with( $field, 'NotIn' ):
						$operator = 'NOT IN';
						break;
					default:
						$operator = 'IN';
						break;
				}

				// Set tax query config.
				switch ( $field ) {
					case 'type':
						// If the type is variation, we only need to set the post_type arg.
						if ( 'variation' === $where_args[ $field ] ) {
							$query_args['post_type'] = [ 'product_variation' ];
							break;
						}
						// Otherwise continue to create a tax query.
					case 'typeIn':
						if ( is_array( $where_args[ $field ] ) && in_array( 'variation', $where_args[ $field ], true ) ) {
							$query_args['post_type'] = array_merge( $this->post_type, [ 'product_variation' ] );
						}
						$tax_query[] = [ // phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed.DisallowedPartiallyKeyed
							'relation' => 'OR',
							[
								'taxonomy' => 'product_type',
								'field'    => 'slug',
								'terms'    => $where_args[ $field ],
							],
							[
								'taxonomy' => 'product_type',
								'field'    => 'id',
								'operator' => 'NOT EXISTS',
							],
						];
						break;
					case 'typeNotIn':
					case 'category':
					case 'categoryIn':
					case 'categoryNotIn':
					case 'tag':
					case 'tagIn':
					case 'tagNotIn':
						// Get terms.
						$terms = $where_args[ $field ];
						if ( ! is_array( $terms ) ) {
							$terms = [ $terms ];
						}

						// Get term taxonomy IDs for complex tax queries.
						$term_taxonomy_ids = [];
						foreach ( $terms as $term_slug ) {
							$term = get_term_by( 'slug', $term_slug, $taxonomy );
							if ( ! $term || is_wp_error( $term ) ) {
								continue;
							}
							$term_taxonomy_ids[] = $term->term_taxonomy_id;
						}
						$tax_query[] = [
							'taxonomy' => $taxonomy,
							'field'    => 'term_taxonomy_id',
							'terms'    => $term_taxonomy_ids,
							'operator' => $operator,
						];
						break;
					case 'categoryId':
					case 'categoryIdIn':
					case 'categoryIdNotIn':
					case 'tagId':
					case 'tagIdIn':
					case 'tagIdNotIn':
						$tax_query[] = [
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $where_args[ $field ],
							'operator' => $operator,
						];
						break;
				}//end switch
			}//end if
		}//end foreach

		// Filter by attribute and term.
		if ( ! empty( $where_args['attribute'] ) && ! empty( $where_args['attributeTerm'] ) ) {
			if ( in_array( $where_args['attribute'], \wc_get_attribute_taxonomy_names(), true ) ) {
				$tax_query[] = [
					'taxonomy' => $where_args['attribute'],
					'field'    => 'slug',
					'terms'    => $where_args['attributeTerm'],
				];
			}
		}

		if ( empty( $where_args['type'] ) && empty( $where_args['typeIn'] ) && ! empty( $where_args['supportedTypesOnly'] )
			&& true === $where_args['supportedTypesOnly'] ) {
			$supported_types = array_keys( WP_GraphQL_WooCommerce::get_enabled_product_types() );
			$terms           = ! empty( $where_args['typeNotIn'] )
				? array_diff( $supported_types, $where_args['typeNotIn'] )
				: $supported_types;
			$tax_query[]     = [
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $terms,
			];
		}

		if ( isset( $where_args['featured'] ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			if ( $where_args['featured'] ) {
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ $product_visibility_term_ids['featured'] ],
				];
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ $product_visibility_term_ids['exclude-from-catalog'] ],
					'operator' => 'NOT IN',
				];
			} else {
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ $product_visibility_term_ids['featured'] ],
					'operator' => 'NOT IN',
				];
			}
		}//end if

		// Handle visibility.
		if ( ! empty( $where_args['visibility'] ) ) {
			switch ( $where_args['visibility'] ) {
				case 'search':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-search' ],
						'operator' => 'NOT IN',
					];
					break;
				case 'catalog':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog' ],
						'operator' => 'NOT IN',
					];
					break;
				case 'visible':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog', 'exclude-from-search' ],
						'operator' => 'NOT IN',
					];
					break;
				case 'hidden':
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog', 'exclude-from-search' ],
						'operator' => 'AND',
					];
					break;
			}//end switch
		}//end if

		if ( ! empty( $where_args['rating'] ) ) {
			$rating       = $where_args['rating'];
			$rating_terms = [];
			foreach ( $rating as $value ) {
				$rating_terms[] = 'rated-' . $value;
			}
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => $rating_terms,
			];
		}
	
		// Process "taxonomyFilter".
		$tax_filter_query = [];
		if ( ! empty( $where_args['taxonomyFilter'] ) ) {
			$taxonomy_query = $where_args['taxonomyFilter'];
			$relation       = ! empty( $taxonomy_query['relation'] ) ? $taxonomy_query['relation'] : 'AND';

			if ( ! empty( $taxonomy_query['filters'] ) ) {
				$tax_groups = [];
				foreach ( $taxonomy_query['filters'] as $filter ) {
					$common = [
						'taxonomy' => $filter['taxonomy'],
						'operator' => ! empty( $filter['operator'] ) ? $filter['operator'] : 'IN',
					];

					if ( ! empty( $filter['ids'] ) ) {
						$tax_groups[] = array_merge(
							$common,
							[
								'field' => 'ID',
								'terms' => $filter['ids'],
							]
						);
					}

					if ( ! empty( $filter['terms'] ) ) {
						$tax_groups[] = array_merge(
							$common,
							[
								'field' => 'slug',
								'terms' => $filter['terms'],
							]
						);
					}
				}//end foreach

				if ( ! empty( $tax_groups ) ) {
					array_push( $tax_filter_query, ...$tax_groups );
				}

				if ( 1 < count( $tax_filter_query ) ) {
					$tax_filter_query['relation'] = $relation;
				}
			}//end if
		}//end if

		if ( ! empty( $tax_filter_query ) ) {
			$tax_query[] = $tax_filter_query;
		}

		if ( 1 < count( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$query_args['tax_query'] = $tax_query;
		}

		$meta_query = [];
		if ( ! empty( $where_args['sku'] ) ) {
			$meta_query[] = [
				'key'     => '_sku',
				'value'   => $where_args['sku'],
				'compare' => 'LIKE',
			];
		}

		if ( ! empty( $where_args['minPrice'] ) ) {
			$query_args['min_price'] = floatval( $where_args['minPrice'] );
		}

		if ( ! empty( $where_args['maxPrice'] ) ) {
			$query_args['max_price'] = floatval( $where_args['maxPrice'] );
		}

		if ( isset( $where_args['stockStatus'] ) ) {
			$meta_query[] = [
				'key'     => '_stock_status',
				'value'   => $where_args['stockStatus'],
				'compare' => is_array( $where_args['stockStatus'] ) ? 'IN' : '=',
			];
		}

		if ( ! empty( $meta_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$query_args['meta_query'] = $meta_query;
		}

		if ( isset( $where_args['onSale'] ) && is_bool( $where_args['onSale'] ) ) {
			$on_sale_key = $where_args['onSale'] ? 'post__in' : 'post__not_in';
			$on_sale_ids = \wc_get_product_ids_on_sale();

			$on_sale_ids                = empty( $on_sale_ids ) ? [ 0 ] : $on_sale_ids;
			$query_args[ $on_sale_key ] = $on_sale_ids;
		}

		

		/**
		 * {@inheritDoc}
		 */
		$query_args = apply_filters(
			'graphql_map_input_fields_to_wp_query',
			$query_args,
			$where_args,
			$this->source,
			$this->args,
			$this->context,
			$this->info,
			$this->post_type
		);

		/**
		 * Filter the input fields
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the WP_Query
		 *
		 * @param array       $args       The mapped query arguments
		 * @param array       $where_args Query "where" args
		 * @param mixed       $source     The query results for a query calling this
		 * @param array       $all_args   All of the arguments for the query (not just the "where" args)
		 * @param \WPGraphQL\AppContext  $context    The AppContext object
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       The ResolveInfo object
		 * @param mixed|string|array      $post_type  The post type for the query
		 */
		$query_args = apply_filters_deprecated(
			'graphql_map_input_fields_to_product_query',
			[
				$query_args,
				$where_args,
				$this->source,
				$this->args,
				$this->context,
				$this->info,
				$this->post_type,
			],
			'0.9.0',
			'graphql_map_input_fields_to_wp_query'
		);

		return $query_args;
	}

	/**
	 * Wrapper for "WC_Connection_Functions::is_valid_post_offset()"
	 *
	 * @param integer $offset Post ID.
	 *
	 * @return bool
	 */
	public function is_valid_offset( $offset ) {
		return (bool) wc_get_product( $offset );
	}

	/**
	 * Adds meta query to the query args.
	 * 
	 * @param array $value Meta query.
	 * 
	 * @return \WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver
	 */
	public function add_meta_query( $value ) {
		if ( ! empty( $this->query_args['meta_query'] ) ) {
			$this->query_args['meta_query'] = $value; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		} else {
			$this->query_args['meta_query'][]           = $value;
			$this->query_args['meta_query']['relation'] = 'AND';
		}

		return $this;
	}

	/**
	 * Adds tax query to the query args.
	 *
	 * @param array $value Tax query.
	 *
	 * @return \WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver
	 */
	public function add_tax_query( $value ) {
		if ( empty( $this->query_args['tax_query'] ) ) {
			$this->query_args['tax_query'] = $value; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		} else {
			$this->query_args['tax_query'][]           = $value;
			$this->query_args['tax_query']['relation'] = 'AND';
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_page_info() {
		$page_info          = parent::get_page_info();
		$page_info['found'] = $this->query->found_posts;

		return $page_info;
	}
}
