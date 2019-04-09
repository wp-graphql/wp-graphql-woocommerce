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
	/**
	 * Confirms the uses has the privileges to query Products
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
		$post_type_obj = get_post_type_object( 'product' );
		$query_args    = array(
			'post_type'      => 'product',
			'post_parent'    => 0,
			'post_status'    => current_user_can( $post_type_obj->cap->edit_posts ) ? 'any' : 'publish',
			'perm'           => 'readable',
			'no_rows_found'  => true,
			'fields'         => 'ids',
			'posts_per_page' => min( max( absint( $first ), absint( $last ), 10 ), $this->query_amount ) + 1,
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
					if ( empty( $query_args['tax_query'] ) ) {
						$query_args['tax_query'] = array();
					}
					$query_args['tax_query'][] = array( // WPCS: slow query ok.
						'taxonomy' => $this->source->taxonomy,
						'terms'    => array( $this->source->term_id ),
						'field'    => 'term_id',
					);
					break;
			}
		}

		if ( isset( $query_args['post__in'] ) && empty( $query_args['post__in'] ) ) {
			$query_args['post__in'] = array( '0' );
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
		return ! empty( $this->query->posts ) ? $this->query->posts : [];
	}

	/**
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to WP_Query
	 * friendly keys. There's probably a cleaner/more dynamic way to approach this, but
	 * this was quick. I'd be down to explore more dynamic ways to map this, but for
	 * now this gets the job done.
	 *
	 * @param array $where_args - arguments being used to filter query.
	 *
	 * @access public
	 * @return array
	 */
	public function sanitize_input_fields( array $where_args ) {
		$args = array();

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
				'terms'    => $where_args[ $key ],
			);
		}

		if ( ! empty( $where_args['featured'] ) && is_bool( $where_args['featured'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
				'operator' => true === $where_args['featured'] ? 'IN' : 'NOT IN',
			);
		}

		if ( ! empty( $tax_query ) && 1 > count( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query; // WPCS: slow query ok.
		}

		$meta_query = array();
		if ( ! empty( $where_args['sku'] ) ) {
			$skus = explode( ',', $where_args['sku'] );
			if ( 1 < count( $skus ) ) {
				$skus[] = $where_args['sku'];
			}

			$meta_query[] = array(
				'key'     => '_sku',
				'value'   => $skus,
				'compare' => 'IN',
			);
		}

		if ( ! empty( $where_args['minPrice'] ) || ! empty( $where_args['maxPrice'] ) ) {
			$prices = array(
				'min_price' => isset( $where_args['minPrice'] ) ? $where_args['minPrice'] : 0,
				'max_price' => isset( $where_args['maxPrice'] ) ? $where_args['maxPrice'] : 9999999999,
			);

			$meta_query[] = \wc_get_min_max_price_meta_query( $prices );
		}

		if ( ! empty( $where_args['inStock'] ) && is_bool( $where_args['inStock'] ) ) {
			$meta_query[] = array(
				'key'   => '_stock_status',
				'value' => true === $where_args['inStock'] ? 'instock' : 'outofstock',
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

		return $args;
	}
}
