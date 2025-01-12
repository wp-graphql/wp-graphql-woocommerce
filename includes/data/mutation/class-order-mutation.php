<?php
/**
 * Defines helper functions for executing mutations related to the orders.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\Utils\Utils;


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
	 * @param integer|null|false                   $order_id  Order ID.
	 * @throws \GraphQL\Error\UserError  Error locating order.
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

		if ( ! $order_id ) {
			return apply_filters(
				"graphql_woocommerce_authorized_to_{$mutation}_orders",
				current_user_can( $post_type_object->cap->edit_posts ),
				$order_id,
				$input,
				$context,
				$info
			);
		}

		/** @var false|\WC_Order $order */
		$order = \wc_get_order( $order_id );
		if ( false === $order ) {
			throw new UserError(
				sprintf(
					/* translators: %d: Order ID */
					__( 'Failed to find order with ID of %d.', 'wp-graphql-woocommerce' ),
					$order_id
				)
			);
		}

		$post_type = get_post_type( $order_id );
		if ( false === $post_type ) {
			throw new UserError( __( 'Failed to identify the post type of the order.', 'wp-graphql-woocommerce' ) );
		}

		// Return true if user is owner or admin.
		$is_owner = 0 !== get_current_user_id() && $order->get_customer_id() === get_current_user_id();
		$is_admin = \wc_rest_check_post_permissions( $post_type, 'edit', $order_id );
		return $is_owner || $is_admin;
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
		$order = new \WC_Order();

		$order->set_currency( ! empty( $input['currency'] ) ? $input['currency'] : get_woocommerce_currency() );
		$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
		$order->set_customer_ip_address( \WC_Geolocation::get_ip_address() );
		$order->set_customer_user_agent( wc_get_user_agent() );

		$order_id = $order->save();

		$order_keys = [
			'status'       => 'status',
			'customerId'   => 'customer_id',
			'customerNote' => 'customer_note',
			'parent'       => 'parent',
			'createdVia'   => 'created_via',
		];

		$args = [ 'order_id' => $order_id ];
		foreach ( $input as $key => $value ) {
			if ( array_key_exists( $key, $order_keys ) ) {
				$args[ $order_keys[ $key ] ] = $value;
			}
		}

		/**
		 * Action called before order is created.
		 *
		 * @param array                                $input   Input data describing order.
		 * @param \WPGraphQL\AppContext                $context Request AppContext instance.
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
	 * @throws \Exception  Failed to retrieve order.
	 *
	 * @return void
	 */
	public static function add_items( $input, $order_id, $context, $info ) {
		/** @var \WC_Order|false $order */
		$order = \WC_Order_Factory::get_order( $order_id );
		if ( false === $order ) {
			throw new \Exception( __( 'Failed to retrieve order.', 'wp-graphql-woocommerce' ) );
		}

		$item_group_keys = [
			'lineItems'     => 'line_item',
			'shippingLines' => 'shipping',
			'feeLines'      => 'fee',
		];

		$order_items = [];
		foreach ( $input as $key => $group_items ) {
			if ( array_key_exists( $key, $item_group_keys ) ) {
				$type                 = $item_group_keys[ $key ];
				$order_items[ $type ] = [];

				/**
				 * Action called before an item group is added to an order.
				 *
				 * @param array                                $group_items  Items data being added.
				 * @param \WC_Order                            $order        Order object.
				 * @param \WPGraphQL\AppContext                $context      Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info         Request ResolveInfo instance.
				 */
				do_action( "graphql_woocommerce_before_{$type}s_added_to_order", $group_items, $order, $context, $info );

				foreach ( $group_items as $item_data ) {
					$item = self::set_item(
						$item_data,
						$type,
						$order,
						$context,
						$info
					);

					/**
					 * Action called before an item group is added to an order.
					 *
					 * @param \WC_Order_Item                       $item      Order item object.
					 * @param array                                $item_data Item data being added.
					 * @param \WC_Order                            $order     Order object.
					 * @param \WPGraphQL\AppContext                $context   Request AppContext instance.
					 * @param \GraphQL\Type\Definition\ResolveInfo $info      Request ResolveInfo instance.
					 */
					do_action( "graphql_woocommerce_before_{$type}_added_to_order", $item, $item_data, $order, $context, $info );

					if ( 0 === $item->get_id() ) {
						$order->add_item( $item );
						$order_items[ $type ][] = $item;
					} else {
						$item->save();
						$order_items[ $type ][] = $item;
					}
				}

				/**
				 * Action called after an item group is added to an order, and before the order has been saved with the new items.
				 *
				 * @param array                                $group_items  Item data being added.
				 * @param \WC_Order                            $order        Order object.
				 * @param \WPGraphQL\AppContext                $context      Request AppContext instance.
				 * @param \GraphQL\Type\Definition\ResolveInfo $info         Request ResolveInfo instance.
				 */
				do_action( "graphql_woocommerce_after_{$type}s_added_to_order", $group_items, $order, $context, $info );
			}//end if
		}//end foreach

		/**
		 * Action called after all items have been added and right before the new items have been saved.
		 *
		 * @param array<string, array<\WC_Order_Item>> $order_items Order items.
		 * @param \WC_Order                            $order       WC_Order instance.
		 * @param array                                $input       Input data describing order.
		 * @param \WPGraphQL\AppContext                $context     Request AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info        Request ResolveInfo instance.
		 */
		do_action( 'graphql_woocommerce_before_new_order_items_save', $order_items, $order, $input, $context, $info );

		$order->save();
	}

	/**
	 *
	 * @param array<string, mixed>                 $item_data  Item data.
	 * @param string                               $type       Item type.
	 * @param \WC_Order                            $order      Order object.
	 * @param \WPGraphQL\AppContext                $context    AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info       ResolveInfo instance.
	 *
	 * @return \WC_Order_Item
	 */
	public static function set_item( $item_data, $type, $order, $context, $info ) {
		$item_id    = ! empty( $item_data['id'] ) ? $item_data['id'] : 0;
		$item_class = self::get_order_item_classname( $type, $item_id );

		/** @var \WC_Order_Item $item */
		$item = new $item_class( $item_id );

		/**
		 * Filter the order item object before it is created.
		 *
		 * @param \WC_Order_Item                       $item       Order item object.
		 * @param array                                $item_data  Item data.
		 * @param \WC_Order                            $order      Order object.
		 * @param \WPGraphQL\AppContext                $context    AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       ResolveInfo instance.
		 */
		$item = apply_filters( "graphql_create_order_{$type}_object", $item, $item_data, $order, $context, $info );

		self::map_input_to_item( $item, $item_data, $type );

		/**
		 * Action called after an order item is created.
		 *
		 * @param \WC_Order_Item                       $item       Order item object.
		 * @param array                                $item_data  Item data.
		 * @param \WC_Order                            $order      Order object.
		 * @param \WPGraphQL\AppContext                $context    AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info       ResolveInfo instance.
		 */
		do_action( "graphql_create_order_{$type}", $item, $item_data, $order, $context, $info );

		return $item;
	}

	/**
	 * Get order item class name.
	 *
	 * @param string $type Order item type.
	 * @param int    $id  Order item ID.
	 *
	 * @return string
	 */
	public static function get_order_item_classname( $type, $id = 0 ) {
		$classname = false;
		switch ( $type ) {
			case 'line_item':
			case 'product':
				$classname = 'WC_Order_Item_Product';
				break;
			case 'coupon':
				$classname = 'WC_Order_Item_Coupon';
				break;
			case 'fee':
				$classname = 'WC_Order_Item_Fee';
				break;
			case 'shipping':
				$classname = 'WC_Order_Item_Shipping';
				break;
			case 'tax':
				$classname = 'WC_Order_Item_Tax';
				break;
		}

		$classname = apply_filters( 'woocommerce_get_order_item_classname', $classname, $type, $id ); // phpcs:ignore WordPress.NamingConventions

		return $classname;
	}

	/**
	 * Return array of item mapped with the provided $item_keys and extracts $meta_data
	 *
	 * @param \WC_Order_Item &$item      Order item.
	 * @param array          $input      Item input data.
	 * @param string         $type       Item type.
	 *
	 * @throws \Exception Failed to retrieve connected product.
	 *
	 * @return void
	 */
	protected static function map_input_to_item( &$item, $input, $type ) {
		$item_keys = self::get_order_item_keys( $type );

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
			$product = ( ! empty( $item['product_id'] ) )
				? wc_get_product( $item['product_id'] )
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
			if ( is_callable( [ $item, "set_{$key}" ] ) ) {
				$item->{"set_{$key}"}( $value );
			}
		}

		// Update item meta data if any is found.
		if ( empty( $meta_data ) ) {
			return;
		}

		foreach ( $meta_data as $entry ) {
			$exists = $item->get_meta( $entry['key'], true, 'edit' );
			if ( '' !== $exists && $exists !== $entry['value'] ) {
				$item->update_meta_data( $entry['key'], $entry['value'] );
			} else {
				$item->add_meta_data( $entry['key'], $entry['value'] );
			}
		}
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
		} elseif ( ! empty( $data['variation_id'] ) ) {
			$product_id = (int) $data['variation_id'];
		} elseif ( ! empty( $data['product_id'] ) ) {
			$product_id = (int) $data['product_id'];
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
		 * @param \WC_Order                            $order   WC_Order instance.
		 * @param array                                $props   Order props array.
		 * @param \WPGraphQL\AppContext                $context Request AppContext instance.
		 * @param \GraphQL\Type\Definition\ResolveInfo $info    Request ResolveInfo instance.
		 */
		do_action( 'graphql_woocommerce_before_order_meta_save', $order, $input, $context, $info );

		$order->save_meta_data();
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
	 * @param string $customer_id  ID of customer for order.
	 *
	 * @return bool
	 */
	public static function validate_customer( $customer_id ) {
		$id = Utils::get_database_id_from_id( $customer_id );
		if ( ! $id ) {
			return false;
		}

		if ( false === get_user_by( 'id', $id ) ) {
			return false;
		}

		// Make sure customer is part of blog.
		if ( is_multisite() && ! is_user_member_of_blog( $id ) ) {
			add_user_to_blog( get_current_blog_id(), $id, 'customer' );
		}

		return true;
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
