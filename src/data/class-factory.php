<?php
/**
 * Factory
 *
 * This class serves as a factory for all the resolvers of queries and mutations.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Attribute_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Download_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Posts_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Terms_Connection_Resolver;

/**
 * Class Factory
 */
class Factory {
	/**
	 * Resolves WooCommerce post-types connections
	 *
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 * @param string      $post_type  - Connection target post-type.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_wc_posts_connection( $source, array $args, $context, ResolveInfo $info, $post_type ) {
		$resolver = new WC_Posts_Connection_Resolver( $source, $args, $context, $info, $post_type );
		return $resolver->get_connection();
	}

	/**
	 * Resolves WooCommerce term connections
	 *
	 * @param mixed       $source        - Connection parent resolver.
	 * @param array       $args          - Connection arguments.
	 * @param AppContext  $context       - AppContext object.
	 * @param ResolveInfo $info          - ResolveInfo object.
	 * @param string      $taxonomy_name - Connection target taxonomy.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_wc_terms_connection( $source, array $args, $context, ResolveInfo $info, $taxonomy_name ) {
		$resolver = new WC_Terms_Connection_Resolver( $source, $args, $context, $info, $taxonomy_name );
		return $resolver->get_connection();
	}

	/**
	 * Resolves product attribute connections
	 *
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_attribute_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Product_Attribute_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves product download connections
	 *
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_download_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Product_Download_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}
}
