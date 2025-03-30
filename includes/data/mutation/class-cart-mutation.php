<?php
/**
 * Defines helper functions for executing mutations related to the cart.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Cart_Mutation
 */
class Cart_Mutation {
	/**
	 * Retrieve `cart` output field defintion
	 *
	 * @param bool $fallback  Should cart be retrieved, if not provided in payload.
	 * @return array
	 */
	public static function get_cart_field( $fallback = false ) {
		return [
			'type'    => 'Cart',
			'resolve' => static function ( $payload ) use ( $fallback ) {
				$cart = ! empty( $payload['cart'] ) ? $payload['cart'] : null;

				if ( is_null( $cart ) && $fallback ) {
					$cart = Factory::resolve_cart();
				}
				return $cart;
			},
		];
	}

	/**
	 * Returns a cart item.
	 *
	 * @param array                                $input   Input data describing cart item.
	 * @param \WPGraphQL\AppContext                $context AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info    Query info.
	 *
	 * @throws \GraphQL\Error\UserError Missing/Invalid input.
	 *
	 * @return array
	 */
	public static function prepare_cart_item( $input, $context, $info ) {
		if ( empty( $input['productId'] ) ) {
			throw new UserError( __( 'No product ID provided', 'wp-graphql-woocommerce' ) );
		}

		if ( ! \wc_get_product( $input['productId'] ) ) {
			throw new UserError( __( 'No product found matching the ID provided', 'wp-graphql-woocommerce' ) );
		}

		$cart_item_args   = [ $input['productId'] ];
		$cart_item_args[] = ! empty( $input['quantity'] ) ? $input['quantity'] : 1;
		$cart_item_args[] = ! empty( $input['variationId'] ) ? $input['variationId'] : 0;
		$cart_item_args[] = ! empty( $input['variation'] ) ? self::prepare_attributes( $input['productId'], $input['variation'] ) : [];
		$cart_item_args[] = ! empty( $input['extraData'] )
			? json_decode( $input['extraData'], true )
			: [];

		return apply_filters( 'graphql_woocommerce_new_cart_item_data', $cart_item_args, $input, $context, $info );
	}

	/**
	 * Processes the provided variation attributes data for the cart.
	 *
	 * @param int   $product_id      Product ID.
	 * @param array $variation_data  Variation data.
	 *
	 * @return array
	 *
	 * @throws \GraphQL\Error\UserError  Invalid cart attribute provided.
	 */
	public static function prepare_attributes( $product_id, array $variation_data = [] ) {
		$product = wc_get_product( $product_id );

		// Bail if bad product ID.
		if ( ! $product ) {
			throw new UserError(
				sprintf(
					/* translators: %s: product ID */
					__( 'No product found matching the ID provided: %s', 'wp-graphql-woocommerce' ),
					$product_id
				)
			);
		}

		$attribute_names = array_keys( $product->get_attributes() );

		$attributes = [];
		foreach ( $variation_data as $attribute ) {
			$attribute_name = $attribute['attributeName'];
			if ( in_array( "pa_{$attribute_name}", $attribute_names, true ) ) {
				$attribute_name = "pa_{$attribute_name}";
			} elseif ( ! in_array( $attribute_name, $attribute_names, true ) ) {
				throw new UserError(
					sprintf(
						/* translators: %1$s: attribute name, %2$s: product name */
						__( '%1$s is not a valid attribute of the product: %2$s.', 'wp-graphql-woocommerce' ),
						$attribute_name,
						$product->get_name()
					)
				);
			}

			$attribute_value = ! empty( $attribute['attributeValue'] ) ? $attribute['attributeValue'] : '';
			$attribute_key   = "attribute_{$attribute_name}";

			$attributes[ $attribute_key ] = $attribute_value;
		}

		return $attributes;
	}

	/**
	 * Returns an array of cart items.
	 *
	 * @param array                                $input    Input data describing cart items.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     Query info.
	 * @param string                               $mutation Mutation type.
	 *
	 * @return array
	 * @throws \GraphQL\Error\UserError Cart item not found message.
	 */
	public static function retrieve_cart_items( $input, $context, $info, $mutation = '' ) {
		$items = null;
		// If "all" flag provided, retrieve all cart items.
		if ( ! empty( $input['all'] ) ) {
			$items = array_values( \WC()->cart->get_cart() );
		}

		// If keys are provided and cart items haven't been retrieve yet,
		// retrieve the cart items by key.
		if ( ! empty( $input['keys'] ) && null === $items ) {
			$items = [];
			foreach ( $input['keys'] as $key ) {
				$item = \WC()->cart->get_cart_item( $key );
				if ( empty( $item ) ) {
					/* translators: Cart item not found message */
					throw new UserError( sprintf( __( 'No cart item found with the key: %s', 'wp-graphql-woocommerce' ), $key ) );
				}
				$items[] = $item;
			}
		}

		return apply_filters( 'graphql_woocommerce_retrieve_cart_items', $items, $input, $context, $info, $mutation );
	}

	/**
	 * Return array of data to be when defining a cart fee.
	 *
	 * @param array                                $input   input data describing cart item.
	 * @param \WPGraphQL\AppContext                $context AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info    query info.
	 *
	 * @return array
	 */
	public static function prepare_cart_fee( $input, $context, $info ) {
		$cart_item_args = [
			$input['name'],
			$input['amount'],
			! empty( $input['taxable'] ) ? $input['taxable'] : false,
			! empty( $input['taxClass'] ) ? $input['taxClass'] : '',
		];

		return apply_filters( 'graphql_woocommerce_new_cart_fee_data', $cart_item_args, $input, $context, $info );
	}

	/**
	 * Validates coupon and checks if application is possible
	 *
	 * @param string $code    Coupon code.
	 * @param string $reason  Reason for failure.
	 *
	 * @return bool
	 */
	public static function validate_coupon( $code, &$reason = '' ) {
		// Get the coupon.
		$the_coupon = new \WC_Coupon( $code );

		// Prevent adding coupons by post ID.
		if ( strtoupper( $the_coupon->get_code() ) !== strtoupper( $code ) ) {
			$reason = __( 'No coupon found with the code provided', 'wp-graphql-woocommerce' );
			return false;
		}

		// Check it can be used with cart.
		if ( ! $the_coupon->is_valid() ) {
			$reason = $the_coupon->get_error_message();
			return false;
		}

		// Check if applied.
		if ( \WC()->cart->has_discount( $code ) ) {
			$reason = __( 'This coupon has already been applied to the cart', 'wp-graphql-woocommerce' );
			return false;
		}

		return true;
	}

	/**
	 * Validates shipping method by checking comparing against shipping package.
	 *
	 * @param string  $shipping_method  Shipping method being validated.
	 * @param integer $index            Index of the shipping package.
	 * @param string  $reason           Reason for failure.
	 *
	 * @return bool
	 */
	public static function validate_shipping_method( $shipping_method, $index, &$reason = '' ) {
		// Get available shipping packages.
		$available_packages = \WC()->cart->needs_shipping()
			? \WC()->shipping()->calculate_shipping( \WC()->cart->get_shipping_packages() )
			: [];

		if ( ! isset( $available_packages[ $index ] ) ) {
			$reason = sprintf(
				/* translators: %d: Package index */
				__( 'No shipping packages available for corresponding index %d', 'wp-graphql-woocommerce' ),
				$index
			);

			return false;
		}

		$package           = $available_packages[ $index ];
		$chosen_rate_index = array_search( $shipping_method, wp_list_pluck( $package['rates'], 'id' ), true );

		if ( false !== $chosen_rate_index ) {
			return true;
		}

		$product_names = [];
		foreach ( $package['contents'] as $item_id => $values ) {
			$product_names[ $item_id ] = \html_entity_decode( $values['data']->get_name() . ' &times;' . $values['quantity'] );
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );

		$reason = sprintf(
			/* translators: %1$s: shipping method ID, %2$s: package contents */
			__( '"%1$s" is not an available shipping method for shipping package "%2$s"', 'wp-graphql-woocommerce' ),
			$shipping_method,
			implode( ', ', $product_names )
		);

		return false;
	}

	/**
	 * Validates and prepares posted shipping methods for the user session.
	 *
	 * @param array $posted_shipping_methods  Chosen shipping methods.
	 *
	 * @throws \GraphQL\Error\UserError  Invalid shipping method.
	 *
	 * @return array<string,string>
	 */
	public static function prepare_shipping_methods( $posted_shipping_methods ) {
		/**
		 * Get current shipping methods.
		 *
		 * @var array<string,string> $chosen_shipping_methods
		 */
		$chosen_shipping_methods = \WC()->session->get( 'chosen_shipping_methods' );

		// Update current shipping methods.
		foreach ( $posted_shipping_methods as $package => $chosen_method ) {
			if ( empty( $chosen_method ) ) {
				continue;
			}

			$reason = '';
			if ( self::validate_shipping_method( $chosen_method, $package, $reason ) ) {
				$chosen_shipping_methods[ $package ] = $chosen_method;
			} else {
				throw new UserError( $reason );
			}
		}

		return $chosen_shipping_methods;
	}

	/**
	 * Validate CartItemQuantityInput item.
	 *
	 * @param array $item  CartItemQuantityInput object.
	 *
	 * @return boolean
	 */
	public static function item_is_valid( array $item ) {
		if ( empty( $item['key'] ) ) {
			return false;
		}
		if ( ! isset( $item['quantity'] ) || ! is_numeric( $item['quantity'] ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Checks for errors thrown by the QL_Session_Handler during session token validation.
	 *
	 * @throws \GraphQL\Error\UserError If GRAPHQL_DEBUG is set to true and errors found.
	 *
	 * @return void
	 */
	public static function check_session_token() {
		$token_invalid = apply_filters( 'graphql_woocommerce_session_token_errors', null );
		if ( $token_invalid ) {
			throw new UserError( $token_invalid );
		}

		\WC()->cart->get_cart_from_session();
	}
}
