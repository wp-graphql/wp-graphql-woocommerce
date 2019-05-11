<?php
/**
 * ConnectionResolver - Product_Connection_Resolver
 *
 * Resolves connections to Products
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Model\Coupon;
use WPGraphQL\Extensions\WooCommerce\Model\Customer;
use WPGraphQL\Extensions\WooCommerce\Model\Order;
use WPGraphQL\Extensions\WooCommerce\Model\Product;
use WPGraphQL\Model\Term;

/**
 * Class Product_Connection_Resolver
 */
class Product_Connection_Resolver extends AbstractConnectionResolver {
	use WC_Connection_Resolver {
		sanitize_input_fields as sanitize_shared_input_fields;
	}

	/**
	 * The name of the post type, or array of post types the connection resolver is resolving for
	 *
	 * @var string|array
	 */
	protected $post_type;

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

		add_filter(
			'woocommerce_product_data_store_cpt_get_products_query',
			array( &$this, 'product_query_filter' ),
			10,
			2
		);
		/**
		 * Call the parent construct to setup class data
		 */
		parent::__construct( $source, $args, $context, $info );
	}

	/**
	 * Applies price meta_query args to product query args
	 *
	 * @param array $wp_query_args - Formatted query args.
	 * @param array $query_vars    - Raw query args.
	 *
	 * @return array
	 */
	public function product_query_filter( $wp_query_args, $query_vars ) {
		if ( empty( $query_vars['meta_query'] ) ) {
			return $wp_query_args;
		}

		$price_meta_query = array_filter(
			$query_vars['meta_query'],
			function ( $query ) {
				return ! empty( $query['key'] ) ? '_price' === $query['key'] : false;
			}
		);

		if ( ! empty( $price_meta_query ) ) {
			$wp_query_args['meta_query'] = array_merge( // WPCS: slow query ok.
				$wp_query_args['meta_query'],
				$price_meta_query
			);
		}

		return $wp_query_args;
	}

	/**
	 * Confirms the uses has the privileges to query Products
	 *
	 * @return bool
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Returns the attribute meta for the taxonomy provided
	 *
	 * @param string $taxonomy - The taxonomy name.
	 *
	 * @return string
	 */
	private function get_attribute_meta_key( $taxonomy ) {
		return 'attribute_' . strtolower(
			preg_replace( '/([A-Z])/', '_$1', $taxonomy )
		);
	}

	/**
	 * Creates query arguments array
	 */
	public function get_query_args() {
		global $wpdb;

		// Prepare for later use.
		$last  = ! empty( $this->args['last'] ) ? $this->args['last'] : null;
		$first = ! empty( $this->args['first'] ) ? $this->args['first'] : null;

		// Set the $query_args based on various defaults and primary input $args.
		$post_type_obj = get_post_type_object( $this->post_type );
		$query_args    = array(
			'post_type'           => $this->post_type,
			'post_parent'         => 0,
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
		$input_fields = [];
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

		// Determine where we're at in the Graph and adjust the query context appropriately.
		if ( true === is_object( $this->source ) ) {
			switch ( true ) {
				case is_a( $this->source, Coupon::class ):
					if ( 'excludedProducts' === $this->info->fieldName ) {
						$query_args['post__in'] = isset( $query_args['post__in'] )
							? array_intersect( $this->source->excluded_product_ids, $query_args['post__in'] )
							: $this->source->excluded_product_ids;
					} else {
						$query_args['post__in'] = isset( $query_args['post__in'] )
							? array_intersect( $this->source->product_ids, $query_args['post__in'] )
							: $this->source->product_ids;
					}
					break;

				case is_a( $this->source, Customer::class ):
					break;

				case is_a( $this->source, Product::class ):
					if ( 'related' === $this->info->fieldName ) {
						$query_args['post__in'] = isset( $query_args['post__in'] )
							? array_intersect( $this->source->related_ids, $query_args['post__in'] )
							: $this->source->related_ids;
					} elseif ( 'upsell' === $this->info->fieldName ) {
						$query_args['post__in'] = isset( $query_args['post__in'] )
							? array_intersect( $this->source->upsell_ids, $query_args['post__in'] )
							: $this->source->upsell_ids;
					} elseif ( 'crossSell' === $this->info->fieldName ) {
						$query_args['post__in'] = isset( $query_args['post__in'] )
							? array_intersect( $this->source->cross_sell_ids, $query_args['post__in'] )
							: $this->source->cross_sell_ids;
					} elseif ( 'grouped' === $this->info->fieldName ) {
						$query_args['post__in'] = isset( $query_args['post__in'] )
							? array_intersect( $this->source->grouped_ids, $query_args['post__in'] )
							: $this->source->grouped_ids;
					} elseif ( 'variations' === $this->info->fieldName ) {
						$query_args['post_parent'] = $this->source->ID;
						$query_args['post__in']    = isset( $query_args['post__in'] )
							? array_intersect( $this->source->variation_ids, $query_args['post__in'] )
							: $this->source->variation_ids;
						$query_args['post_type']   = 'product_variation';
					}
					break;

				case is_a( $this->source, Term::class ):
					if ( 'variations' === $this->info->fieldName ) {
						unset( $query_args['post_parent'] );
						$query_args['post_type'] = 'product_variation';
						$variation_ids           = $wpdb->get_col(
							$wpdb->prepare(
								"SELECT ID
								FROM {$wpdb->prefix}posts
								WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND meta_value = %s)
								AND post_type = 'product_variation'",
								$this->get_attribute_meta_key( $this->source->taxonomyName ),
								$this->source->slug
							)
						);
						if ( ! empty( $variation_ids ) ) {
							$variation_ids          = array_map( 'absint', $variation_ids );
							$query_args['post__in'] = isset( $query_args['post__in'] )
								? array_intersect( $variation_ids, $query_args['post__in'] )
								: $variation_ids;
						}
					} else {
						if ( empty( $query_args['tax_query'] ) ) {
							$query_args['tax_query'] = array(); // WPCS: slow query ok.
						}
						$query_args['tax_query'][] = array( // WPCS: slow query ok.
							'taxonomy' => $this->source->taxonomyName,
							'field'    => 'term_id',
							'terms'    => $this->source->term_id,
						);
					}
					break;
			}
		}

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $this->args;

		if ( isset( $query_args['post__in'] ) && empty( $query_args['post__in'] ) ) {
			$query_args['post__in'] = array( '0' );
		}

		if ( ! current_user_can( $post_type_obj->cap->read_private_posts ) ) {
			if ( empty( $query_args['tax_query'] ) ) {
				$query_args['tax_query'] = array(); // WPCS: slow query ok.
			}
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'slug',
				'terms'    => array( 'exclude-from-catalog', 'exclude-from-search' ),
				'operator' => 'NOT IN',
			);
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
		 * Filter the $query args to allow folks to customize queries programmatically
		 *
		 * @param array       $query_args The args that will be passed to the WP_Query
		 * @param mixed       $source     The source that's passed down the GraphQL queries
		 * @param array       $args       The inputArgs on the field
		 * @param AppContext  $context    The AppContext passed down the GraphQL tree
		 * @param ResolveInfo $info       The ResolveInfo passed down the GraphQL tree
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
		return new \WP_Query( $this->get_query_args() );
	}

	/**
	 * Return an array of items from the query
	 *
	 * @return array
	 */
	public function get_items() {
		return ! empty( $this->query->posts ) ? $this->query->posts : array();
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
		$args = $this->sanitize_shared_input_fields( $where_args );

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
			'type'          => 'product_type',
			'typeIn'        => 'product_type',
			'typeNotIn'     => 'product_type',
			'categoryName'  => 'product_cat',
			'category'      => 'product_cat',
			'categoryIn'    => 'product_cat',
			'categoryNotIn' => 'product_cat',
			'tagSlug'       => 'product_tag',
			'tagId'         => 'product_tag',
			'tagIn'         => 'product_tag',
			'tagNotIn'      => 'product_tag',
		);

		foreach ( $taxonomy_args as $field => $taxonomy ) {
			if ( ! empty( $where_args[ $field ] ) ) {
				switch ( $field ) {
					case 'type':
					case 'typeIn':
					case 'typeNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $where_args[ $field ],
						);
						break;
					case 'categoryName':
					case 'categoryNameIn':
					case 'categoryNameNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $where_args[ $field ],
						);
						break;
					case 'category':
					case 'categoryIn':
					case 'categoryNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $where_args[ $field ],
						);
						break;
					case 'tag':
					case 'tagSlugIn':
					case 'tagSlugNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $where_args[ $field ],
						);
						break;
					case 'tagId':
					case 'tagIn':
					case 'tagNotIn':
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $where_args[ $field ],
						);
						break;
				}

				if ( \wc_graphql_ends_with( $field, 'NotIn' ) ) {
					$key                           = max( array_keys( $tax_query ) );
					$tax_query[ $key ]['operator'] = 'NOT IN';
				} elseif ( \wc_graphql_ends_with( $field, 'In' ) ) {
					$key                           = max( array_keys( $tax_query ) );
					$tax_query[ $key ]['operator'] = 'IN';
				}
			}
		}

		// Filter by attribute and term.
		if ( ! empty( $where_args['attribute'] ) && ! empty( $where_args['attributeTerm'] ) ) {
			if ( in_array( $where_args['attribute'], \wc_get_attribute_taxonomy_names(), true ) ) {
				$tax_query[] = array(
					'taxonomy' => $where_args['attribute'],
					'field'    => 'term_id',
					'terms'    => $where_args['attributeTerm'],
				);
			}
		}

		if ( ! empty( $where_args['type'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $where_args['type'],
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
		if ( ! empty( $where_args['visibility'] ) && current_user_can( $post_type_obj->cap->read_private_posts ) ) {
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
}
