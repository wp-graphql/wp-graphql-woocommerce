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

use Automattic\WooCommerce\StoreApi\Utilities\ProductQuery;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Utils\Utils;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce;

/**
 * Class Product_Connection_Resolver
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
	 * @var \Automattic\WooCommerce\StoreApi\Utilities\ProductQuery
	 */
	private $products_query;

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

		$this->products_query = new ProductQuery();

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
		if ( ! empty( $query_args['search'] ) && empty( $query_args['orderby'] ) ) {
			/**
			 * Don't order search results by title (causes funky issues with cursors)
			 */
			$query_args['search_orderby_title'] = false;
			$query_args                         = array_merge( $query_args, \WC()->query->get_catalog_ordering_args( 'relevance', isset( $last ) ? 'ASC' : 'DESC' ) );
		}

		if ( empty( $query_args['orderby'] ) ) {
			$query_args = array_merge( $query_args, \WC()->query->get_catalog_ordering_args( 'menu_order', isset( $last ) ? 'ASC' : 'DESC' ) );
		}

		$has_offset = isset( $last ) ? $this->get_before_offset() : $this->get_after_offset();

		$offset_product = null;
		if ( $has_offset ) {
			/** @var \WPGraphQL\WooCommerce\Model\Product|null $offset_model */
			$offset_model = $this->get_loader()->load( $has_offset );

			/** @var \WC_Product|\WC_Product_Variable|null $offset_product */
			$offset_product = $offset_model ? $offset_model->as_WC_Data() : null;
		}

		if ( $offset_product && 'price' === $query_args['orderby'] ) {
			/** @var array<float>|float|null $price */
			$price = is_a( $offset_product, 'WC_Product_Variable' )
				? $offset_product->get_variation_price()
				: $offset_product->get_price();
			if ( 'ASC' === $query_args['order'] ) {
				if ( is_array( $price ) ) {
					$price = reset( $price );
				}
				$query_args['graphql_cursor_compare_by_price_key']   = 'wc_product_meta_lookup.min_price';
				$query_args['graphql_cursor_compare_by_price_value'] = $price;
			} else {
				if ( is_array( $price ) ) {
					$price = end( $price );
				}
				$query_args['graphql_cursor_compare_by_price_key']   = 'wc_product_meta_lookup.max_price';
				$query_args['graphql_cursor_compare_by_price_value'] = $price;
			}
		} elseif ( $offset_product && 'popularity' === $query_args['orderby'] ) {
			$query_args['graphql_cursor_compare_by_popularity_value'] = $offset_product->get_total_sales();
			$query_args['graphql_cursor_compare_by_popularity_key']   = 'wc_product_meta_lookup.total_sales';
		} elseif ( $offset_product && 'rating' === $query_args['orderby'] ) {
			$query_args['graphql_cursor_compare_by_rating_value'] = $offset_product->get_average_rating();
			$query_args['graphql_cursor_compare_by_rating_key']   = 'wc_product_meta_lookup.average_rating';
		} elseif ( $offset_product && 'comment_count' === $query_args['orderby'] ) {
			$query_args['graphql_cursor_compare_by_comment_count_value'] = $offset_product->get_rating_count();
			$query_args['graphql_cursor_compare_by_comment_count_key']   = 'wc_product_meta_lookup.rating_count';
			$query_args['graphql_cursor_compare_by_rating_value']        = $offset_product->get_average_rating();
			$query_args['graphql_cursor_compare_by_rating_key']          = 'wc_product_meta_lookup.average_rating';
		} elseif ( $offset_product && 'menu_order title' === $query_args['orderby'] ) {
			$query_args['orderby'] = [
				'menu_order' => $query_args['order'],
				'post_title' => isset( $this->args['last'] ) ? 'ASC' : 'DESC',
			];
			unset( $query_args['order'] );
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
		 * @param array<string, mixed>|null            $args       The inputArgs on the field
		 * @param \WPGraphQL\AppContext                $context The AppContext passed down the GraphQL tree
		 * @param \GraphQL\Type\Definition\ResolveInfo $info The ResolveInfo passed down the GraphQL tree
		 */
		return apply_filters( 'graphql_product_connection_query_args', $query_args, $this->source, $this->args, $this->context, $this->info );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_query() {
		add_filter( 'posts_clauses', [ $this->products_query, 'add_query_clauses' ], 10, 2 );

		// Temporary fix for the search query.
		if ( ! empty( $this->query_args['search'] ) ) {
			$this->query_args['fulltext_search'] = $this->query_args['search'];
			unset( $this->query_args['search'] );
			add_filter( 'posts_clauses', [ $this, 'add_search_query_clause' ], 10, 2 );
		}

		return new \WP_Query();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ids_from_query() {
		// Run query and get IDs.
		$ids = $this->query->query( $this->query_args );

		if ( ! empty( $this->query_args['fulltext_search'] ) ) {
			remove_filter( 'posts_clauses', [ $this, 'add_search_query_clause' ], 10 );
		}

		remove_filter( 'posts_clauses', [ $this->products_query, 'add_query_clauses' ], 10 );

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
				'_sale_price_dates_from',
				'_sale_price_dates_to',
				'total_sales',
			]
		);
	}

	/**
	 * This function replaces the default product query search query clause with a clause searching the product's description, short description and slug.
	 *
	 * @param array     $args      The query arguments.
	 * @param \WP_Query $wp_query  The WP_Query object.
	 * @return array
	 */
	public function add_search_query_clause( $args, $wp_query ) {
		global $wpdb;
		if ( empty( $wp_query->get( 'fulltext_search' ) ) ) {
			return $args;
		}

		$search         = '%' . $wpdb->esc_like( $wp_query->get( 'fulltext_search' ) ) . '%';
		$search_query   = $wpdb->prepare( " AND ( $wpdb->posts.post_title LIKE %s OR $wpdb->posts.post_name LIKE %s OR wc_product_meta_lookup.sku LIKE %s OR $wpdb->posts.post_content LIKE %s OR $wpdb->posts.post_excerpt LIKE %s ) ", $search, $search, $search, $search, $search );
		$args['where'] .= $search_query;

		if ( ! strstr( $args['join'], 'wc_product_meta_lookup' ) ) {
			$args['join'] .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
		}

		return $args;
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

			$orderby = $orderby_input['field'];
			$order   = ! empty( $orderby_input['order'] ) ? $orderby_input['order'] : $default_order;
			// Set the order to DESC if orderby is popularity.
			if ( 'popularity' === $orderby || 'rating' === $orderby ) {
				$order = 'DESC';
			}
			$query_args = array_merge( $query_args, \wc()->query->get_catalog_ordering_args( $orderby, $order ) );
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
			graphql_debug(
				__( 'The "attribute" and "attributeTerm" arguments have been deprecated. Please use the "attributes" argument instead.', 'wp-graphql-woocommerce' ),
			);
			if ( in_array( $where_args['attribute'], \wc_get_attribute_taxonomy_names(), true ) ) {
				$tax_query[] = [
					'taxonomy' => $where_args['attribute'],
					'field'    => 'slug',
					'terms'    => $where_args['attributeTerm'],
				];
			}
		}

		// Filter by attributes.
		if ( ! empty( $where_args['attributes'] ) && ! empty( $where_args['attributes']['queries'] ) ) {
			$attributes  = $where_args['attributes']['queries'];
			$att_queries = [];

			foreach ( $attributes as $attribute ) {
				if ( empty( $attribute['ids'] ) && empty( $attribute['terms'] ) ) {
					continue;
				}

				if ( ! in_array( $attribute['taxonomy'], \wc_get_attribute_taxonomy_names(), true ) ) {
					continue;
				}

				$operator = isset( $attribute['operator'] ) ? $attribute['operator'] : 'IN';

				if ( ! empty( $attribute['terms'] ) ) {
					foreach ( $attribute['terms'] as $term ) {
						$att_queries[] = [
							'taxonomy' => $attribute['taxonomy'],
							'field'    => 'slug',
							'terms'    => $term,
							'operator' => $operator,
						];
					}
				}

				if ( ! empty( $attribute['ids'] ) ) {
					foreach ( $attribute['ids'] as $id ) {
						$att_queries[] = [
							'taxonomy' => $attribute['taxonomy'],
							'field'    => 'term_id',
							'terms'    => $id,
							'operator' => $operator,
						];
					}
				}
			}

			if ( 1 < count( $att_queries ) ) {
				$relation = ! empty( $where_args['attributes']['relation'] ) ? $where_args['attributes']['relation'] : 'AND';
				if ( 'NOT_IN' === $relation ) {
					graphql_debug( __( 'The "NOT_IN" relation is not supported for attributes. Please use "IN" or "AND" instead.', 'wp-graphql-woocommerce' ) );
					$relation = 'IN';
				}

				$tax_query[] = array_merge(
					[ 'relation' => $relation ],
					$att_queries
				);
			} else {
				$tax_query = array_merge( $tax_query, $att_queries );
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
			$query_args['min_price'] = number_format( $where_args['minPrice'], 2, '', '' );
		}

		if ( ! empty( $where_args['maxPrice'] ) ) {
			$query_args['max_price'] = number_format( $where_args['maxPrice'], 2, '', '' );
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
