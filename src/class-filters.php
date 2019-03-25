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

use WPGraphQL\Extensions\WooCommerce\Data\Gallery_Connection_Query_Arg;
use WPGraphQL\Extensions\WooCommerce\Data\Coupon_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Product_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Data\Loader\WC_Loader;

/**
 * Class Filters
 */
class Filters {
	/**
	 * Register filters
	 */
	public static function load() {
		/**
		 * Filter connections query info
		 */
		add_filter(
			'graphql_connection_query_info',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'graphql_connection_query_info',
			),
			10,
			2
		);

		/**
		 * Filter connection query arguments
		 */
		add_filter(
			'graphql_post_object_connection_query_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'graphql_post_object_connection_query_args',
			),
			10,
			5
		);

		/**
		 * Filter WooCommerce taxonomies
		 */
		add_filter(
			'register_post_type_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'register_post_type_args',
			),
			10,
			2
		);

		/**
		 * Filter WooCommerce taxonomies
		 */
		add_filter(
			'register_taxonomy_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'register_taxonomy_args',
			),
			10,
			2
		);

		add_filter(
			'resolve_post_object_loader',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'resolve_post_object_loader',
			),
			10,
			4
		);
	}

	/**
	 * Filter - graphql_connection_query_info
	 */
	public static function graphql_connection_query_info( $query_info, $query ) {
		$query_info = Product_Connection_Resolver::query_info_filter( $query_info, $query );
		return $query_info;
	}

	/**
	 * Filter - graphql_post_object_connection_query_args
	 */
	public static function graphql_post_object_connection_query_args( $query_args, $source, $args, $context, $info ) {
		return Gallery_Connection_Query_Arg::fromProduct( $query_args, $source, $args, $context, $info );
	}

	/**
	 * Filter - register_post_types
	 */
	public static function register_post_type_args( $args, $post_type ) {
		if ( 'product' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'product';
			$args['graphql_plural_name'] = 'products';
		}
		if ( 'product_variation' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productVariation';
			$args['graphql_plural_name'] = 'productVariations';
		}
		if ( 'shop_coupon' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'coupon';
			$args['graphql_plural_name'] = 'coupons';
		}
		if ( 'shop_order' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'order';
			$args['graphql_plural_name'] = 'orders';
		}
		if ( 'shop_order_refund' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'refund';
			$args['graphql_plural_name'] = 'refunds';
		}

		return $args;
	}

	/**
	 * Filter - register_taxonomy_args
	 */
	public static function register_taxonomy_args( $args, $taxonomy ) {
		if ( 'product_type' === $taxonomy ) {
			$args['show_in_graphql'] 		 = true;
			$args['graphql_single_name'] = 'productType';
			$args['graphql_plural_name'] = 'productTypes';
		}

		if ( 'product_visibility' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'visibleProduct';
			$args['graphql_plural_name'] = 'visibleProducts';
		}

		if ( 'product_cat' === $taxonomy ) {
			$args['show_in_graphql'] = true;
			$args['graphql_single_name'] = 'productCategory';
			$args['graphql_plural_name'] = 'productCategories';
		}

		if ( 'product_tag' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productTag';
			$args['graphql_plural_name'] = 'productTags';
		}

		if ( 'product_shipping_class' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'shippingClass';
			$args['graphql_plural_name'] = 'shippingClasses';
		}

		return $args;
	}

	/**
	 * Filter - resolve_post_object_loader
	 */
	public static function resolve_post_object_loader( $loader, $post_id, $context, $post_type ) {
		$wc_post_types = array(
			'shop_coupon',
			'product',
			'product_variation',
			'shop_order',
			'shop_order_refund',
		);

		if ( in_array( $post_type, $wc_post_types ) ) {
			$loader = 'WCLoader';
		}
		return $loader;
	}
}
