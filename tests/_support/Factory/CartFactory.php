<?php
/**
 * Factory class for the WooCommerce's Cart data objects.
 *
 * @since v0.8.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Cart factory class for testing.
 */
class CartFactory {
	/**
	 * Add products to the cart.
	 *
	 * @param array ...$products Product to be added to the cart.
	 *
	 * @return array
	 */
	public function add( ...$products ) {
		$keys = [];

		foreach ( $products as $product ) {
			if ( gettype( $product ) === 'array' ) {
				if ( empty( $product['product_id'] ) ) {
					codecept_debug( $product );
					codecept_debug( 'IS AN INVALID CART ITEM' );
					continue;
				}

				$keys[] = WC()->cart->add_to_cart(
					$product['product_id'],
					! empty( $product['quantity'] ) ? $product['quantity'] : 1,
					! empty( $product['variation_id'] ) ? $product['variation_id'] : 0,
					! empty( $product['variation'] ) ? $product['variation'] : [],
					! empty( $product['cart_item_data'] ) ? $product['cart_item_data'] : []
				);
			} else {
				WC()->cart->add_to_cart( $product, 1 );
			}
		}

		return $keys;
	}

	public function remove( ...$keys ) {
		foreach ( $keys as $key ) {
			$success = \WC()->cart->remove_cart_item( $key );
			if ( false === $success ) {
				codecept_debug( "FAILED TO REMOVE ITEM {$key} FROM CART." );
			}
		}
	}
}
