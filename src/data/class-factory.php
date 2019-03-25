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

/**
 * Class Factory
 */
class Factory {
	/**
	 * Returns the coupon for the ID
	 *
	 * @param int $id ID of the coupon being retrieved
	 *
	 * @throws UserError
	 * @since  0.0.1
	 * @return \WP_Coupon
	 * @access public
	 */
	public static function resolve_coupon( $id, $context ) {
        if ( empty( $id ) || ! absint( $id ) ) {
            return null;
        }
        $post_id = absint( $id );
        $context->WC_Loader->buffer( [ $post_id ] );
        return new Deferred( function () use ( $post_id, $context ) {
            return $context->WC_Loader->load( $post_id );
        });
    }

	/**
	 * Resolves Coupon connection
	 */
	public static function resolve_coupon_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Coupon_Connection_Resolver( $source, $args, $context, $info, 'shop_coupon' );
		return $resolver->get_connection();
	}

	/**
	 * Returns the product for the ID
	 *
	 * @param int $id ID of the product being retrieved
	 *
	 * @throws UserError
	 * @since  0.0.1
	 * @return \WP_Product
	 * @access public
	 */
	public static function resolve_product( $id ) {
		$product = new \WC_Product( $id );
		if ( empty( $product ) ) {
			throw new UserError( sprintf( __( 'No product was found with the ID: %1$s', 'wp-graphql-woocommerce' ), $id ) );
		}

		return $product;
	}

	/**
	 * Resolves Product connection
	 */
	public static function resolve_product_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Product_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves ProductCategory connection
	 */
	public static function resolve_product_category_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new WC_Term_Connection_Resolver( 'product_cat' );
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves ProductTag connection
	 */
	public static function resolve_product_tag_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new WC_Term_Connection_Resolver( 'product_tag' );
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves ProductAttribute connection
	 */
	public static function resolve_product_attribute_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Product_Attribute_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves ProductDownload connection
	 */
	public static function resolve_product_download_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new Product_Download_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}
}
