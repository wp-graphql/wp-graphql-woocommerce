<?php

namespace WPGraphQL\Extensions\WooCommerce\Data;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class Factory
 *
 * This class serves as a factory for all the resolvers for queries and mutations.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since   0.0.1
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
  public static function resolve_coupon( $id ) {
    $coupon = new \WC_Coupon( $id );
		if ( empty( $coupon ) ) {
			throw new UserError( sprintf( __( 'No coupon was found with the ID: %1$s', 'wp-graphql-woocommerce' ), $id ) );
    }
    
    return $coupon;
  }

  public static function resolve_coupon_connection( $source, array $args, $context, ResolveInfo $info ) {
    $resolver = new CouponConnectionResolver();
		return $resolver->resolve( $source, $args, $context, $info );
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
	
	public static function resolve_product_connection( $source, array $args, $context, ResolveInfo $info ) {
    $resolver = new ProductConnectionResolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

}