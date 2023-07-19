<?php
/**
 * Defines helper functions for executing mutations related to the orders.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;

/**
 * Class - Order_Mutation
 */
class Order_Mutation {
	/**
	 * Filterable authentication function.
	 *
	 * @param array                                $input     Input data describing order.
	 * @param \WPGraphQL\AppContext                $context   AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info      ResolveInfo instance.
	 * @param string                               $mutation  Mutation being executed.
	 * @param integer|null                         $order_id  Order ID.
	 *
	 * @return boolean
	 */
	public static function authorized( $input, $context, $info, $mutation = 'create', $order_id = null ) {
		/**
		 * Get order post type.
		 *
		 * @var \WP_Post_Type $post_type_object
		 */
		$post_type_object = get_post_type_object( 'shop_order' );

		return apply_filters(
			"graphql_woocommerce_authorized_to_{$mutation}_orders",
			current_user_can(
				'delete' === $mutation
					? $post_type_object->cap->delete_posts
					: $post_type_object->cap->edit_posts
			),
			$order_id,
			$input,
			$context,
			$info
		);
	}

	/**
	 * Create an order.
	 *
	 * @param array                                $input    Input data describing order.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance.
	 *
	 * @return integer
	 *
	 * @throws \GraphQL\Error\UserError  Error creating order.
	 */
	public static function create_order( $input, $context, $info ) {
		$order_keys = [
			'status'       => 'status',
			'customerId'   => 'customer_id',
			'customerNote' => 'customer_note',
			'parent'       => 'parent',
			'createdVia'   => 'created_via',
			'orderId'      => 'order_id',
		];

		$args = [];
		foreach ( $input as $key => $value ) {
			if ( array_key_exists( $key, $order_keys ) ) {
				$args[ $order_keys[ $key ] ] = $value;
			}
		}

		/**
		 * Action called before order is created.
		 *
		 * @param array       $input   Input data describing order.
		 * @param \WPGraphQL\AppContext  $context Request AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
		 */
		do_action( 'graphql_woocommerce_before_order_create', $input, $context, $info );

		$order = \wc_create_order( $args );
		if ( is_wp_error( $order ) ) {
			throw new UserError( $order->get_error_code() . $order->get_error_message() );
		}

		/**
		 * Action called after order is created.
		 *
		 * @param \WC_Order    $order   WC_Order instance.
		 * @param array       $input   Input data describing order.
		 * @param \WPGraphQL\AppContext  $context Request AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
		 */
		do_action( 'graphql_woocommerce_after_order_create', $order, $input, $context, $info );

		return $order->get_id();
	}

	/**
	 * Add items to order.
	 *
	 * @param array                                $input     Input data describing order.
	 * @param int                                  $order_id  Order object.
	 * @param \WPGraphQL\AppContext                $context   AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info      ResolveInfo instance.
	 *
	 * @return void
	 */
	public static function add_items( $input, $order_id, $context, $info ) {
		$item_group_keys = [
			'lineItems'     => 'line_item',
			'shippingLines' => 'shipping',
			'feeLines'      => 'fee',
		];

		$item_groups = [];
		foreach ( $input as $key => $items ) {
			if ( array_key_exists( $key, $item_group_keys ) ) {
				$type = $item_group_keys[ $key ];

				/**
				 * Action called before an item group is added to an order.
				 *
				 * @param array       $items     Item data being added.
				 * @param integer     $order_id  ID of target order.
				 * @param \WPGraphQL\AppContext  $context   Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info      Request ResolveInfo instance.
				 */
				do_action( "graphql_woocommerce_before_{$type}s_added_to_order", $items, $order_id, $context, $info );

				foreach ( $items as $item_data ) {
					// Create Order item.
					$item_id = ( ! empty( $item_data['id'] ) && \WC_Order_Factory::get_order_item( $item_data['id'] ) )
						? $item_data['id']
						: \wc_add_order_item( $order_id, [ 'order_item_type' => $type ] );

					// Continue if order item creation failed.
					if ( ! $item_id ) {
						continue;
					}

					// Add input item data to order item.
					$item_keys = self::get_order_item_keys( $type );
					self::map_input_to_item( $item_id, $item_data, $item_keys, $context, $info );
				}

				/**
				 * Action called after an item group is added to an order.
				 *
				 * @param array       $items     Item data being added.
				 * @param integer     $order_id  ID of target order.
				 * @param \WPGraphQL\AppContext  $context   Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info      Request ResolveInfo instance.
				 */
				do_action( "graphql_woocommerce_after_{$type}s_added_to_order", $items, $order_id, $context, $info );
			}//end if
		}//end foreach
	}

	/**
	 * Return array of item mapped with the provided $item_keys and extracts $meta_data
	 *
	 * @param integer                              $item_id    Order item ID.
	 * @param array                                $input      Item input data.
	 * @param array                                $item_keys  Item key map.
	 * @param \WPGraphQL\AppContext                $context    AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info       ResolveInfo instance.
	 *
	 * @throws \Exception  Failed to retrieve order item | Failed to retrieve connected product.
	 *
	 * @return int
	 */
	protected static function map_input_to_item( $item_id, $input, $item_keys, $context, $info ) {
		$order_item = \WC_Order_Factory::get_order_item( $item_id );
		if ( ! is_object( $order_item ) ) {
			throw new \Exception( __( 'Failed to retrieve order item.', 'wp-graphql-woocommerce' ) );
		}

		$args      = [];
		$meta_data = null;
		foreach ( $input as $key => $value ) {
			if ( array_key_exists( $key, $item_keys ) ) {
				$args[ $item_keys[ $key ] ] = $value;
			} elseif ( 'metaData' === $key ) {
				$meta_data = $value;
			} else {
				$args[ $key ] = $value;
			}
		}

		// Calculate to subtotal/total for line items.

		if ( isset( $args['quantity'] ) ) {
			$product = ( ! empty( $order_item['product_id'] ) )
				? wc_get_product( $order_item['product_id'] )
				: wc_get_product( self::get_product_id( $args ) );
			if ( ! is_object( $product ) ) {
				throw new \Exception( __( 'Failed to retrieve product connected to order item.', 'wp-graphql-woocommerce' ) );
			}

			$total            = wc_get_price_excluding_tax( $product, [ 'qty' => $args['quantity'] ] );
			$args['subtotal'] = ! empty( $args['subtotal'] ) ? $args['subtotal'] : $total;
			$args['total']    = ! empty( $args['total'] ) ? $args['total'] : $total;
		}

		// Set item props.
		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $order_item, "set_{$key}" ] ) ) {
				$order_item->{"set_{$key}"}( $value );
			}
		}

		// Update item meta data if any is found.
		if ( 0 !== $item_id && ! empty( $meta_data ) ) {
			// Update item meta data.
			self::update_item_meta_data( $item_id, $meta_data, $context, $info );
		}

		return $order_item->save();
	}

	/**
	 * Returns array of item keys by item type.
	 *
	 * @param string $type  Order item type.
	 *
	 * @return array
	 */
	protected static function get_order_item_keys( $type ) {
		switch ( $type ) {
			case 'line_item':
				return [
					'productId'   => 'product_id',
					'variationId' => 'variation_id',
					'taxClass'    => 'tax_class',
				];

			case 'shipping':
				return [
					'name'        => 'order_item_name',
					'methodTitle' => 'method_title',
					'methodId'    => 'method_id',
					'instanceId'  => 'instance_id',
				];

			case 'fee':
				return [
					'name'      => 'name',
					'taxClass'  => 'tax_class',
					'taxStatus' => 'tax_status',
				];
			default:
				/**
				 * Allow filtering of order item keys for unknown item types.
				 *
				 * @param array  $item_keys  Order item keys.
				 * @param string $type       Order item type slug.
				 */
				return apply_filters( 'woographql_get_order_item_keys', [], $type );
		}//end switch
	}

	/**
	 * Gets the product ID from the SKU or line item data ID.
	 *
	 * @param array $data  Line item data.
	 *
	 * @return integer
	 * @throws \GraphQL\Error\UserError When SKU or ID is not valid.
	 */
	protected static function get_product_id( $data ) {
		if ( ! empty( $data['sku'] ) ) {
			$product_id = (int) wc_get_product_id_by_sku( $data['sku'] );
		} elseif ( ! empty( $data['product_id'] ) && empty( $data['variation_id'] ) ) {
			$product_id = (int) $data['product_id'];
		} elseif ( ! empty( $data['variation_id'] ) ) {
			$product_id = (int) $data['variation_id'];
		} else {
			throw new UserError( __( 'Product ID or SKU is required.', 'wp-graphql-woocommerce' ) );
		}

		return $product_id;
	}

	/**
	 * Create/Update order item meta data.
	 *
	 * @param int                                  $item_id    Order item ID.
	 * @param array                                $meta_data  Array of meta data.
	 * @param \WPGraphQL\AppContext                $context    AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info       ResolveInfo instance.
	 *
	 * @throws \GraphQL\Error\UserError|\Exception  Invalid item input | Failed to retrieve order item.
	 *
	 * @return void
	 */
	protected static function update_item_meta_data( $item_id, $meta_data, $context, $info ) {
		$item = \WC_Order_Factory::get_order_item( $item_id );
		if ( ! is_object( $item ) ) {
			throw new \Exception( __( 'Failed to retrieve order item.', 'wp-graphql-woocommerce' ) );
		}

		foreach ( $meta_data as $entry ) {
			$exists = $item->get_meta( $entry['key'], true, 'edit' );
			if ( '' !== $exists && $exists !== $entry['value'] ) {
				\wc_update_order_item_meta( $item_id, $entry['key'], $entry['value'] );
			} else {
				\wc_add_order_item_meta( $item_id, $entry['key'], $entry['value'] );
			}
		}
	}

	/**
	 * Add meta data not set in self::create_order().
	 *
	 * @param int                                  $order_id  Order ID.
	 * @param array                                $input     Order properties.
	 * @param \WPGraphQL\AppContext                $context   AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info      ResolveInfo instance.
	 *
	 * @throws \Exception  Failed to retrieve order.
	 *
	 * @return void
	 */
	public static function add_order_meta( $order_id, $input, $context, $info ) {
		$order = \WC_Order_Factory::get_order( $order_id );
		if ( ! is_object( $order ) ) {
			throw new \Exception( __( 'Failed to retrieve order.', 'wp-graphql-woocommerce' ) );
		}

		foreach ( $input as $key => $value ) {
			switch ( $key ) {
				case 'coupons':
				case 'lineItems':
				case 'shippingLines':
				case 'feeLines':
				case 'status':
					break;
				case 'billing':
				case 'shipping':
					self::update_address( $value, $order_id, $key );
					$order->apply_changes();
					break;
				case 'metaData':
					if ( is_array( $value ) ) {
						foreach ( $value as $meta ) {
							$order->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
						}
					}
					break;
				default:
					$prop = \wc_graphql_camel_case_to_underscore( $key );
					if ( is_callable( [ $order, "set_{$prop}" ] ) ) {
						$order->{"set_{$prop}"}( $value );
					}
					break;
			}//end switch
		}//end foreach

		/**
		 * Action called before changes to order meta are saved.
		 *
		 * @param \WC_Order   $order   WC_Order instance.
		 * @param array       $props   Order props array.
		 * @param \WPGraphQL\AppContext  $context Request AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
		 */
		do_action( 'graphql_woocommerce_before_order_meta_save', $order, $input, $context, $info );

		$order->save();
	}

	/**
	 * Update address.
	 *
	 * @param array   $address   Address data.
	 * @param integer $order_id  WC_Order instance.
	 * @param string  $type      Address type.
	 *
	 * @throws \Exception  Failed to retrieve order.
	 *
	 * @return void
	 */
	protected static function update_address( $address, $order_id, $type = 'billing' ) {
		$order = \WC_Order_Factory::get_order( $order_id );
		if ( ! is_object( $order ) ) {
			throw new \Exception( __( 'Failed to retrieve order.', 'wp-graphql-woocommerce' ) );
		}

		$formatted_address = Customer_Mutation::address_input_mapping( $address, $type );
		foreach ( $formatted_address as $key => $value ) {
			if ( is_callable( [ $order, "set_{$type}_{$key}" ] ) ) {
				$order->{"set_{$type}_{$key}"}( $value );
			}
		}
		$order->save();
	}

	/**
	 * Applies coupons to WC_Order instance
	 *
	 * @param int   $order_id  Order ID.
	 * @param array $coupons   Coupon codes to be applied to order.
	 *
	 * @throws \Exception  Failed to retrieve order.
	 *
	 * @return void
	 */
	public static function apply_coupons( $order_id, $coupons ) {
		$order = \WC_Order_Factory::get_order( $order_id );
		if ( ! is_object( $order ) ) {
			throw new \Exception( __( 'Failed to retrieve order.', 'wp-graphql-woocommerce' ) );
		}

		// Remove all coupons first to ensure calculation is correct.
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			/**
			 * Order item coupon.
			 *
			 * @var \WC_Order_Item_Coupon $coupon
			 */

			$order->remove_coupon( $coupon->get_code() );
		}

		foreach ( $coupons as $code ) {
			$results = $order->apply_coupon( sanitize_text_field( $code ) );
			if ( is_wp_error( $results ) ) {
				do_action( 'graphql_woocommerce_' . $results->get_error_code(), $results, $code, $coupons, $order );
			}
		}

		$order->save();
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
	 * Purge object when creating.
	 *
	 * @param null|\WC_Order|\WPGraphQL\WooCommerce\Model\Order $order         Object data.
	 * @param boolean                                           $force_delete  Delete or put in trash.
	 *
	 * @return bool
	 * @throws \GraphQL\Error\UserError  Failed to delete order.
	 */
	public static function purge( $order, $force_delete = true ) {
		if ( ! empty( $order ) ) {
			return $order->delete( $force_delete );
		}

		return false;
	}
}
