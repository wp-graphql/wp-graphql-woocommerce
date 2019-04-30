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

use WPGraphQL\Extensions\WooCommerce\Data\Connection\Post_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Terms_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Data\Loader\WC_Customer_Loader;
use WPGraphQL\Extensions\WooCommerce\Data\Loader\WC_Post_Crud_Loader;

/**
 * Class Filters
 */
class Filters {
	/**
	 * Stores instance WC_Customer_Loader
	 *
	 * @var WC_Customer_Loader
	 */
	private static $customer_loader;

	/**
	 * Stores instance WC_Post_Crud_Loader
	 *
	 * @var WC_Post_Crud_Loader
	 */
	private static $post_crud_loader;

	/**
	 * Register filters
	 */
	public static function load() {
		add_filter( 'register_taxonomy_args', array( __CLASS__, 'register_taxonomy_args' ), 10, 2 );
		add_filter( 'graphql_data_loaders', array( __CLASS__, 'graphql_data_loaders' ), 10, 2 );
		add_filter(
			'graphql_post_object_connection_query_args',
			array( __CLASS__, 'graphql_post_object_connection_query_args' ),
			10,
			5
		);
		add_filter(
			'graphql_term_object_connection_query_args',
			array( __CLASS__, 'graphql_term_object_connection_query_args' ),
			10,
			5
		);
	}

	/**
	 * Initializes WC_Loader instance
	 *
	 * @param AppContext $context - AppContext.
	 *
	 * @return WC_Post_Crud_Loader
	 */
	public static function post_crud_loader( $context ) {
		if ( is_null( self::$post_crud_loader ) ) {
			self::$post_crud_loader = new WC_Post_Crud_Loader( $context );
		}
		return self::$post_crud_loader;
	}

	/**
	 * Initializes Customer_Loader instance
	 *
	 * @param AppContext $context - AppContext.
	 *
	 * @return WC_Customer_Loader
	 */
	public static function customer_loader( $context ) {
		if ( is_null( self::$customer_loader ) ) {
			self::$customer_loader = new WC_Customer_Loader( $context );
		}
		return self::$customer_loader;
	}

	/**
	 * Registers WooCommerce taxonomies to be used in GraphQL schema
	 *
	 * @param array  $args     - allowed post-types.
	 * @param string $taxonomy - name of taxonomy being checked.
	 *
	 * @return array
	 */
	public static function register_taxonomy_args( $args, $taxonomy ) {
		if ( 'product_type' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productType';
			$args['graphql_plural_name'] = 'productTypes';
		}

		if ( 'product_visibility' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'visibleProduct';
			$args['graphql_plural_name'] = 'visibleProducts';
		}

		if ( 'product_cat' === $taxonomy ) {
			$args['show_in_graphql']     = true;
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

		// Filter product attributes taxonomies.
		$attributes = \WP_GraphQL_WooCommerce::get_product_attribute_taxonomies();
		if ( in_array( $taxonomy, $attributes, true ) ) {
			$singular_name               = graphql_format_field_name( $taxonomy );
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = $singular_name;
			$args['graphql_plural_name'] = \Inflect::pluralize( $singular_name );
		}

		return $args;
	}

	/**
	 * Registers data-loaders to be used when resolving WooCommerce-related GraphQL types
	 *
	 * @param array      $loaders - assigned loaders.
	 * @param AppContext $context - AppContext instance.
	 *
	 * @return array
	 */
	public static function graphql_data_loaders( $loaders, $context ) {
		// WooCommerce customer loader.
		$customer_loader        = self::customer_loader( $context );
		$loaders['wc_customer'] = &$customer_loader;

		// WooCommerce crud object loader.
		$post_crud_loader        = self::post_crud_loader( $context );
		$loaders['wc_post_crud'] = &$post_crud_loader;

		return $loaders;
	}

	/**
	 * Filter PostObjectConnectionResolver's query_args and adds args to used when querying WooCommerce post-types
	 *
	 * @param array       $query_args - WP_Query args.
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return mixed
	 */
	public static function graphql_post_object_connection_query_args( $query_args, $source, $args, $context, $info ) {
		return Post_Connection_Resolver::get_query_args( $query_args, $source, $args, $context, $info );
	}

	/**
	 * Filter TermObjectConnectionResolver's query_args and adds args to used when querying WooCommerce taxonomies
	 *
	 * @param array       $query_args - WP_Term_Query args.
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return mixed
	 */
	public static function graphql_term_object_connection_query_args( $query_args, $source, $args, $context, $info ) {
		return WC_Terms_Connection_Resolver::get_query_args( $query_args, $source, $args, $context, $info );
	}
}
