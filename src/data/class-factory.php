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
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Coupon_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Customer_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Order_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Attribute_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Download_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Refund_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Posts_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Terms_Connection_Resolver;

/**
 * Class Factory
 */
class Factory {
	/**
	 * Returns the Customer store object for the provided user ID
	 *
	 * @param int        $id      - user ID of the customer being retrieved.
	 * @param AppContext $context - AppContext object.
	 *
	 * @return Deferred object
	 * @access public
	 */
	public static function resolve_customer( $id, AppContext $context ) {
		if ( empty( $id ) || ! absint( $id ) ) {
			return null;
		}
		$customer_id = absint( $id );
		$loader      = $context->getLoader( 'wc_customer' );
		$loader->buffer( [ $customer_id ] );
		return new Deferred(
			function () use ( $loader, $customer_id ) {
				return $loader->load( $customer_id );
			}
		);
	}

	/**
	 * Returns the WooCommerce CRUD object for the post ID
	 *
	 * @param int        $id      - post ID of the crud object being retrieved.
	 * @param AppContext $context - AppContext object.
	 *
	 * @return Deferred object
	 * @access public
	 */
	public static function resolve_crud_object( $id, AppContext $context ) {
		if ( empty( $id ) || ! absint( $id ) ) {
			return null;
		}
		$object_id = absint( $id );
		$loader    = $context->getLoader( 'wc_post_crud' );
		$loader->buffer( [ $object_id ] );
		return new Deferred(
			function () use ( $loader, $object_id ) {
				return $loader->load( $object_id );
			}
		);
	}

	/**
	 * Resolves Coupon connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_coupon_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Coupon_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves Customer connections
	 *
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_customer_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Customer_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves Order connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_order_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Order_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves Product connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves ProductAttribute connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_attribute_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Product_Attribute_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves ProductDownload connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_download_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Product_Download_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves Refund connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_refund_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Refund_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}
}
