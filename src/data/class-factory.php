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

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Customer_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Attribute_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Download_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Posts_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Terms_Connection_Resolver;

/**
 * Class Factory
 */
class Factory {
	/**
	 * Returns the coupon for the ID
	 *
	 * @param int        $id      - ID of the coupon being retrieved.
	 * @param AppContext $context - AppContext object.
	 *
	 * @return Deferred object
	 * @access public
	 */
	public static function resolve_customer( $id, $context ) {
		if ( empty( $id ) || ! absint( $id ) ) {
			return null;
		}
		$customer_id = absint( $id );
		$loader      = $context->getLoader( 'customer' );
		$loader->buffer( [ $customer_id ] );
		return new Deferred(
			function () use ( $loader, $customer_id ) {
				return $loader->load( $customer_id );
			}
		);
	}

	/**
	 * Resolves customer connections
	 *
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_customer_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Customer_Connection_Resolver( $source, $args, $context, $info );
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
