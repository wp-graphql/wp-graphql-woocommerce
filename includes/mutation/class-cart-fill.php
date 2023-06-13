<?php
/**
 * Mutation - fillCart
 *
 * Registers mutation for adding cart items, coupons, and shipping methods at once.
 * Designed for minimal use.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.8.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Fill
 */
class Cart_Fill {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'fillCart',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'shippingMethods' => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Shipping methods to be used.', 'wp-graphql-woocommerce' ),
			],
			'coupons'         => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Coupons to be applied to the cart', 'wp-graphql-woocommerce' ),
			],
			'items'           => [
				'type'        => [ 'list_of' => 'CartItemInput' ],
				'description' => __( 'Cart items to be added', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'added'                 => [
				'type'    => [ 'list_of' => 'CartItem' ],
				'resolve' => function ( $payload ) {
					$items = [];
					foreach ( $payload['added'] as $key ) {
						$items[] = \WC()->cart->get_cart_item( $key );
					}

					return $items;
				},
			],
			'applied'               => [
				'type'    => [ 'list_of' => 'AppliedCoupon' ],
				'resolve' => function( $payload ) {
					$codes = $payload['applied'];
					return ! empty( $codes ) ? $codes : null;
				},
			],
			'chosenShippingMethods' => [
				'type'    => [ 'list_of' => 'String' ],
				'resolve' => function( $payload ) {
					$methods = $payload['chosen_shipping_methods'];
					return ! empty( $methods ) ? $methods : null;
				},
			],
			'cartErrors'            => [
				'type'    => [ 'list_of' => 'CartError' ],
				'resolve' => function ( $payload ) {
					$errors         = [];
					$all_error_data = array_merge(
						$payload['invalid_cart_items'],
						$payload['invalid_coupons'],
						$payload['invalid_shipping_methods']
					);

					foreach ( $all_error_data as $error_data ) {
						$cart_error = [];
						switch ( true ) {
							case isset( $error_data['cart_item_data'] ):
								$cart_error         = $error_data['cart_item_data'];
								$cart_error['type'] = 'INVALID_CART_ITEM';
								break;
							case isset( $error_data['code'] ):
								$cart_error = [
									'code' => $error_data['code'],
									'type' => 'INVALID_COUPON',
								];
								break;
							case isset( $error_data['package'] ):
								$cart_error = [
									'package'       => $error_data['package'],
									'chosen_method' => $error_data['chosen_method'],
									'type'          => 'INVALID_SHIPPING_METHOD',
								];
								break;
							default:
								$cart_error = [
									'reasons' => [ 'Unknown error occurred.' ],
									'type'    => 'UNKNOWN',
								];
								break;
						}//end switch

						if ( ! empty( $error_data['reasons'] ) ) {
							$cart_error['reasons'] = $error_data['reasons'];
						} elseif ( $error_data['reason'] ) {
							$cart_error['reasons'] = [ $error_data['reason'] ];
						}

						$errors[] = $cart_error;
					}//end foreach

					return $errors;
				},
			],
			'cart'                  => Cart_Mutation::get_cart_field( true ),
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			Cart_Mutation::check_session_token();

			// Throw error, if no cart item data provided.
			if ( empty( $input['items'] ) ) {
				throw new UserError( __( 'No cart item data provided', 'wp-graphql-woocommerce' ) );
			}

			// Validate cart item input.
			$added              = [];
			$invalid_cart_items = [];
			foreach ( $input['items'] as $cart_item_data ) {
				try {
					// Prepare args for "add_to_cart" from input data.
					$cart_item_args = Cart_Mutation::prepare_cart_item( $cart_item_data, $context, $info );

					// Add item to cart and get cart item key.
					$key = \WC()->cart->add_to_cart( ...$cart_item_args );

					// If cart item key valid, add to payload and continue to next item.
					if ( false !== $key ) {
						$added[] = $key;
						continue;
					}

					// Else capture errors.
					$notices = \WC()->session->get( 'wc_notices' );
					if ( ! empty( $notices['error'] ) ) {
						$reasons = array_column( $notices['error'], 'notice' );
						\wc_clear_notices();

						$invalid_cart_items[] = compact( 'cart_item_data', 'reasons' );
					} else {
						$reason               = __( 'Failed to add cart item. Please check input.', 'wp-graphql-woocommerce' );
						$invalid_cart_items[] = compact( 'cart_item_data', 'reason' );
					}
				} catch ( \Exception $e ) {
					// Get thrown error message.
					$reason = $e->getMessage();

					// Capture error.
					$invalid_cart_items[] = compact( 'cart_item_data', 'reason' );
				}//end try
			}//end foreach

			// Log captured errors.
			if ( ! empty( $invalid_cart_items ) ) {
				graphql_debug( $invalid_cart_items, [ 'type' => 'INVALID_CART_ITEMS' ] );
			}

			// Throw error, if no items added.
			if ( empty( $added ) ) {
				throw new UserError( __( 'Failed to add any cart items. Please check input.', 'wp-graphql-woocommerce' ) );
			}

			$applied         = [];
			$invalid_coupons = [];
			if ( ! empty( $input['coupons'] ) ) {
				foreach ( $input['coupons'] as $code ) {
					$reason = '';
					// If validate and successful applied to cart, return payload.
					if ( Cart_Mutation::validate_coupon( $code, $reason ) && \WC()->cart->apply_coupon( $code ) ) {
						$applied[] = $code;
						continue;
					}

					// If any session error notices, capture them.
					$error_notices = \WC()->session->get( 'wc_notices' );
					if ( empty( $reason ) && is_array( $error_notices ) && ! empty( $error_notices ) ) {
						$reason = implode( ' ', array_column( $error_notices, 'notice' ) );
						\wc_clear_notices();
					}

					// Throw any capture errors.
					if ( empty( $reason ) ) {
						$reason = __( 'Failed to apply coupon. Check for an individual-use coupon on cart.', 'wp-graphql-woocommerce' );
					}

					$invalid_coupons[] = compact( 'code', 'reason' );
				}//end foreach

				if ( ! empty( $invalid_coupons ) ) {
					graphql_debug( $invalid_coupons, [ 'type' => 'INVALID_COUPONS' ] );
				}
			}//end if

			$chosen_shipping_methods  = [];
			$invalid_shipping_methods = [];
			if ( ! empty( $input['shippingMethods'] ) ) {
				$posted_shipping_methods = $input['shippingMethods'];

				// Get current shipping methods.
				$chosen_shipping_methods = \WC()->session->get( 'chosen_shipping_methods' );

				// Update current shipping methods.
				foreach ( $posted_shipping_methods as $package => $chosen_method ) {
					if ( empty( $chosen_method ) ) {
						continue;
					}

					$reason = '';
					if ( Cart_Mutation::validate_shipping_method( $chosen_method, $package, $reason ) ) {
						$chosen_shipping_methods[ $package ] = $chosen_method;
						continue;
					}

					$invalid_shipping_methods[] = compact( 'package', 'chosen_method', 'reason' );
				}

				// Set updated shipping methods in session.
				\WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

				if ( ! empty( $invalid_shipping_methods ) ) {
					graphql_debug( $invalid_shipping_methods, [ 'type' => 'INVALID_SHIPPING_METHODS' ] );
				}
			}//end if

			// Recalculate totals.
			\WC()->cart->calculate_totals();

			// Return payload.
			return compact(
				'added',
				'invalid_cart_items',
				'applied',
				'invalid_coupons',
				'chosen_shipping_methods',
				'invalid_shipping_methods'
			);
		};
	}
}
