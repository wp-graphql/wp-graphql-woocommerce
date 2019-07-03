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
			'woocommerce_order_mutation_skipped_props',
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
					self::update_address( $value, $order, $key );
					break;
				case 'line_items':
				case 'shipping_lines':
				case 'fee_lines':
					if ( is_array( $value ) ) {
						foreach ( $value as $item ) {
							if ( is_array( $item ) ) {
								if ( self::item_is_null( $item ) || ( isset( $item['quantity'] ) && 0 === $item['quantity'] ) ) {
									$order->remove_item( $item['id'] );
								} else {
									self::set_item( $item, $order, $key );
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
	 * @param array $input  Input data describing order.
	 *
	 * @return bool
	 */
	public static function validate_customer( $input ) {
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
	 * Update address.
	 *
	 * @param array    $address  Address data.
	 * @param WC_Order $order    WC_Order instance.
	 * @param string   $type     Address type.
	 */
	protected static function update_address( $address, $order, $type = 'billing' ) {
		$formatted_address = Customer_Mutation::address_input_mapping( $type, $address );
		foreach ( $formatted_address as $key => $value ) {
			if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
				$order->{"set_{$type}_{$key}"}( $value );
			}
		}
	}

	/**
	 * Gets the product ID from the SKU or posted ID.
	 *
	 * @param array $data  Line item data.
	 *
	 * @return int
	 *
	 * @throws UserError When SKU or ID is not valid.
	 */
	protected static function get_product_id( $data ) {
		if ( ! empty( $data['sku'] ) ) {
			$product_id = (int) \wc_get_product_id_by_sku( $data['sku'] );
		} elseif ( ! empty( $data['productId'] ) && empty( $data['variationId'] ) ) {
			$product_id = (int) $data['productId'];
		} elseif ( ! empty( $data['variationId'] ) ) {
			$product_id = (int) $data['variationId'];
		} else {
			throw new UserError( __( 'Product ID or SKU is required.', 'wp-graphql-woocommerce' ) );
		}

		return $product_id;
	}

	/**
	 * Wrapper method to create/update order items.
	 * When updating, the item ID provided is checked to ensure it is associated
	 * with the order.
	 *
	 * @param array    $data   Item data provided in the request body.
	 * @param WC_Order $order  WC_Order instance.
	 * @param string   $type   The item type.
	 *
	 * @throws UserError If item ID is not associated with order.
	 */
	protected static function set_item( $data, $order, $type ) {
		if ( ! empty( $data['id'] ) ) {
			$action = 'update';
		} else {
			$action = 'create';
		}

		$method = 'prepare_' . $type;
		$item   = null;

		// Verify provided line item ID is associated with order.
		if ( 'update' === $action ) {
			$item = $order->get_item( absint( $data['id'] ) );
			if ( ! $item ) {
				throw new UserError( __( 'Order item ID provided is not associated with order.', 'wp-graphql-woocommerce' ) );
			}
		}

		// Prepare item data.
		$item = self::{$method}( $data, $action, $item );

		do_action( 'woocommerce_graphql_set_order_item', $item, $data );

		// If creating the order, add the item to it.
		if ( 'create' === $action ) {
			$item->apply_changes();
			$order->add_item( $item );
		} else {
			$item->save();
		}
	}

	/**
	 * Create or update a line item.
	 *
	 * @param array  $data    Line item data.
	 * @param string $action  'create' to add line item or 'update' to update it.
	 * @param object $item    Passed when updating an item. Null during creation.
	 *
	 * @return WC_Order_Item_Product
	 */
	protected static function prepare_line_items( $data, $action = 'create', $item = null ) {
		$item = is_null( $item )
			? new \WC_Order_Item_Product( ! empty( $data['id'] ) ? $data['id'] : 0 )
			: $item;

		$product = \wc_get_product( self::get_product_id( $data ) );

		if ( $product !== $item->get_product() ) {
			$item->set_product( $product );
			if ( 'create' === $action ) {
				$quantity = isset( $data['quantity'] ) ? $data['quantity'] : 1;
				$total    = \wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
				$item->set_total( $total );
				$item->set_subtotal( $total );
			}
		}

		self::maybe_set_item_props( $item, array( 'name', 'quantity', 'total', 'subtotal', 'taxClass' ), $data );
		self::maybe_set_item_meta_data( $item, $data );

		return $item;
	}

	/**
	 * Create or update an order shipping method.
	 *
	 * @param array  $data    Shipping Item data.
	 * @param string $action  'create' to add shipping or 'update' to update it.
	 * @param object $item     Passed when updating an item. Null during creation.
	 *
	 * @return WC_Order_Item_Shipping
	 *
	 * @throws UserError Invalid data, server error.
	 */
	protected static function prepare_shipping_lines( $data, $action = 'create', $item = null ) {
		$item = is_null( $item )
			? new \WC_Order_Item_Shipping( ! empty( $data['id'] ) ? $data['id'] : '' )
			: $item;

		if ( 'create' === $action ) {
			if ( empty( $data['methodId'] ) ) {
				throw new UserError( __( 'Shipping method ID is required.', 'wp-graphql-woocommerce' ) );
			}
		}

		self::maybe_set_item_props( $item, array( 'methodId', 'methodTitle', 'total' ), $data );
		self::maybe_set_item_meta_data( $item, $data );

		return $item;
	}

	/**
	 * Create or update an order fee.
	 *
	 * @param array  $data    Item data.
	 * @param string $action  'create' to add fee or 'update' to update it.
	 * @param object $item    Passed when updating an item. Null during creation.
	 *
	 * @return WC_Order_Item_Fee
	 *
	 * @throws UserError Invalid data, server error.
	 */
	protected static function prepare_fee_lines( $data, $action = 'create', $item = null ) {
		$item = is_null( $item )
			? new \WC_Order_Item_Fee( ! empty( $data['id'] ) ? $data['id'] : '' )
			: $item;

		if ( 'create' === $action ) {
			if ( empty( $data['name'] ) ) {
				throw new UserError( __( 'Fee name is required.', 'woocommerce' ) );
			}
		}

		self::maybe_set_item_props( $item, array( 'name', 'taxClass', 'taxStatus', 'total' ), $data );
		self::maybe_set_item_meta_data( $item, $data );

		return $item;
	}

	/**
	 * Maybe set an item prop if the value was posted.
	 *
	 * @param WC_Order_Item $item  Order item.
	 * @param string        $prop  Order property.
	 * @param array         $data  Request data.
	 */
	protected static function maybe_set_item_prop( $item, $prop, $data ) {
		if ( isset( $data[ $prop ] ) ) {
			$key = \Inflect::camel_case_to_underscore( $prop );
			$item->{"set_{$key}"}( $data[ $prop ] );
		}
	}

	/**
	 * Maybe set item props if the values were posted.
	 *
	 * @param WC_Order_Item $item   Order item data.
	 * @param string[]      $props  Target properties.
	 * @param array         $data   Prop data.
	 */
	protected static function maybe_set_item_props( $item, $props, $data ) {
		foreach ( $props as $prop ) {
			self::maybe_set_item_prop( $item, $prop, $data );
		}
	}

	/**
	 * Maybe set item meta if posted.
	 *
	 * @param WC_Order_Item $item  Order item data.
	 *
	 * @param array         $data  Request data.
	 */
	protected static function maybe_set_item_meta_data( $item, $data ) {
		if ( ! empty( $data['metaData'] ) && is_array( $data['metaData'] ) ) {
			foreach ( $data['metaData'] as $meta ) {
				if ( isset( $meta['key'] ) ) {
					$value = isset( $meta['value'] ) ? $meta['value'] : null;
					$item->update_meta_data( $meta['key'], $value, isset( $meta['id'] ) ? $meta['id'] : '' );
				}
			}
		}
	}

	/**
	 * Helper method to check if the resource ID associated with the provided item is null.
	 * Items can be deleted by setting the resource ID to null.
	 *
	 * @param array $item Item provided in the request body.
	 * @return bool True if the item resource ID is null, false otherwise.
	 */
	protected static function item_is_null( $item ) {
		$keys = array( 'productId', 'methodId', 'methodTitle', 'name', 'code' );
		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $item ) && is_null( $item[ $key ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Applies coupons to WC_Order instance
	 *
	 * @param array    $coupons  Coupon codes to be applied to order.
	 * @param WC_Order $order    WC_Order instance.
	 */
	public static function apply_coupons( $coupons, $order ) {
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

	/**
	 * Purge object when creating.
	 *
	 * @param WC_Order $order  Object data.
	 *
	 * @return bool
	 */
	protected function purge( $order ) {
		if ( $order instanceof WC_Order ) {
			return $order->delete( true );
		}

		return false;
	}
}
