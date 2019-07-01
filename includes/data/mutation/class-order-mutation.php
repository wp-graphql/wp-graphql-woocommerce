<?php
/**
 * Defines helper functions for executing mutations related to the orders.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;

/**
 * Class - Order_Mutation
 */
class Order_Mutation {
	/**
	 * Returns a order props.
	 *
	 * @param array       $input   Input data describing order.
	 * @param AppContext  $context AppContext instance.
	 * @param ResolveInfo $info    Query info.
	 *
	 * @return array
	 */
	public static function prepare_props( $input, $context, $info ) {
		$props = array();

		// Input keys to be formatted.
		$formatted_props = array(
			'parentId'           => 'parent_id',
			'customerId'         => 'customer_id',
			'transactionId'      => 'transaction_id',
			'customerNote'       => 'customer_note',
			'lineItems'          => 'line_items',
			'shippingLines'      => 'shipping_lines',
			'feeLines'           => 'fee_lines',
			'metaData'           => 'meta_data',
			'paymentMethod'      => 'payment_method',
			'paymentMethodTitle' => 'payment_method_title',
		);

		// Input keys to be skipped.
		$skipped_keys = apply_filters(
			'woocommerce_new_order_s',
			array(
				'status',
				'coupon',
				'isPaid',
			)
		);

		foreach ( $input as $key => $value ) {
			if ( in_array( $key, $skipped_keys, true ) ) {
				continue;
			} elseif ( array_key_exists( $key, $formatted_props ) ) {
				$props[ $formatted_props[ $key ] ] = $value;
			} else {
				$props[ $key ] = $value;
			}
		}

		return apply_filters( 'woocommerce_new_order_data', $props, $input, $context, $info );
	}

	/**
	 * Returns a WC_Order instance.
	 *
	 * @param array       $props    Order properties.
	 * @param AppContext  $context  AppContext instance.
	 * @param ResolveInfo $info     ResolveInfo instance.
	 *
	 * @return WC_Order
	 */
	public static function prepare_order_instance( $props, $context, $info ) {
		$order = new \WC_Order();
		foreach ( $props as $key => $value ) {
			switch ( $key ) {
				case 'coupon_lines':
				case 'status':
					break;
				case 'billing':
				case 'shipping':
					self::update_address( $order, $value, $props );
					break;
				case 'line_items':
				case 'shipping_lines':
				case 'fee_lines':
					if ( is_array( $value ) ) {
						foreach ( $value as $item ) {
							if ( is_array( $item ) ) {
								if ( OrderMutation::item_is_null( $item ) || ( isset( $item['quantity'] ) && 0 === $item['quantity'] ) ) {
									$order->remove_item( $item['id'] );
								} else {
									OrderMutation::set_item( $order, $props, $item );
								}
							}
						}
					}
					break;
				case 'meta_data':
					if ( is_array( $value ) ) {
						foreach ( $value as $meta ) {
							$order->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
						}
					}
					break;
				default:
					if ( is_callable( array( $order, "set_{$key}" ) ) ) {
						$order->{"set_{$key}"}( $value );
					}
					break;
			}
		}

		/**
		 * Filters an object before it is inserted via the GraphQL API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`,
		 * refers to the object type slug.
		 *
		 * @param WC_Order    $order   WC_Order instance.
		 * @param array       $props   Order props array.
		 * @param AppContext  $context Request AppContext instance.
		 * @param ResolveInfo $info    Request ResolveInfo instance.
		 */
		return apply_filters( 'woocommerce_graphql_pre_insert_shop_order_object', $order, $props, $context, $info );
	}

	/**
	 * Validates order customer
	 *
	 * @param array       $input    Input data describing order.
	 * @param AppContext  $context  AppContext instance.
	 * @param ResolveInfo $info     ResolveInfo instance.
	 *
	 * @return bool
	 */
	public static function validate_customer( $input, $context, $info ) {
		if ( ! empty( $input['customerId'] ) ) {
			// Make sure customer exists.
			if ( false === get_user_by( 'id', $input['customerId'] ) ) {
				return false;
			}
			// Make sure customer is part of blog.
			if ( is_multisite() && ! is_user_member_of_blog( $input['customerId'] ) ) {
				add_user_to_blog( get_current_blog_id(), $input['customerId'], 'customer' );
			}

			return true;
		}

		return false;
	}

	/**
	 * Applies coupons to WC_Order instance
	 *
	 * @param array    $coupons  Coupon codes to be applied to order.
	 * @param WC_Order $order    WC_Order instance.
	 */
	public static function apply_coupons( $coupons, &$order ) {
		// Remove all coupons first to ensure calculation is correct.
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			$order->remove_coupon( $coupon->get_code() );
		}

		foreach ( $coupons as $code ) {
			$results = $order->apply_coupon( wc_clean( $code ) );
			if ( is_wp_error( $results ) ) {
				do_action( 'woocommerce_graphql_' . $results->get_error_code(), $results, $code, $coupons, $order );
			}
		}
	}
}
