<?php
/**
 * Factory class for the WooCommerce's order data objects.
 *
 * @since v0.10.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Order factory class for testing.
 */
class OrderFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'status'        => '',
			'customer_id'   => 0,
			'customer_note' => '',
			'parent'        => 0,
			'created_via'   => '',
			'cart_hash'     => '',
			'order_id'      => 0,
		];
	}

	public function create_object( $args ) {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // Required, else wc_create_order throws an exception
		$order                  = \wc_create_order( $args );

		if ( is_wp_error( $order ) ) {
			throw new \Exception( $order->get_error_message( $args->get_error_code() ) );
		}

		// Set props.
		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $order, "set_{$key}" ] ) ) {
				$order->{"set_{$key}"}( $value );
			}
		}

		return $order->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Order && 0 !== absint( $object ) ) {
			$object = $this->get_object_by_id( $object );
		}

		foreach ( $fields as $field => $field_value ) {
			if ( ! is_callable( [ $object, "set_{$field}" ] ) ) {
				throw new \Exception(
					sprintf( '"%1$s" is not a valid %2$s coupon field.', $field, $object->get_type() )
				);
			}

			$object->{"set_{$field}"}( $field_value );
		}

		$object->save();
	}

	public function get_object_by_id( $id ) {
		return \wc_get_order( $id );
	}

	public function createNew( $args = [], $items = [] ) {
		if ( ! isset( $args['customer_id'] ) ) {
			$customer            = new \WC_Customer( $this->factory->customer->create() );
			$args['customer_id'] = $customer->get_id();
		}

		$this->factory->shipping_zone->createLegacyFlatRate();

		$order_id = $this->create( $args );
		$order    = \wc_get_order( $order_id );

		try {
			// Add line items
			if ( ! empty( $items['line_items'] ) ) {
				foreach ( $items['line_items'] as $item ) {
					$order = $this->add_line_item( $order, $item, false );
				}
			} else {
				$random_amount = rand( 1, 3 );
				for ( $i = 0; $i < $random_amount; $i++ ) {
					$order = $this->add_line_item(
						$order,
						[
							'product' => $this->factory->product->createSimple(),
							'qty'     => rand( 1, 6 ),
						],
						false
					);
				}
			}
			$order->save();

			// Add billing / shipping address
			$order = $this->set_to_customer_billing_address( $order, $args['customer_id'], false );
			$order = $this->set_to_customer_shipping_address( $order, $args['customer_id'], false );

			// Add shipping costs
			$shipping_taxes = \WC_Tax::calc_shipping_tax( '10', \WC_Tax::get_shipping_tax_rates() );
			$rate           = new \WC_Shipping_Rate( 'flat_rate_shipping', 'Flat rate shipping', '10', $shipping_taxes, 'flat_rate' );
			$item           = new \WC_Order_Item_Shipping();
			$item->set_props(
				[
					'method_title' => $rate->label,
					'method_id'    => $rate->id,
					'total'        => \wc_format_decimal( $rate->cost ),
					'taxes'        => $rate->taxes,
				]
			);
			foreach ( $rate->get_meta_data() as $key => $value ) {
				$item->add_meta_data( $key, $value, true );
			}
			$order->add_item( $item );

			// Set payment gateway
			$payment_gateways = \WC()->payment_gateways->payment_gateways();
			$order->set_payment_method( $payment_gateways['bacs'] );

			// Set totals
			$order->set_shipping_total( 10 );
			$order->set_discount_total( 0 );
			$order->set_discount_tax( 0 );
			$order->set_cart_tax( 0 );
			$order->set_shipping_tax( 0 );
			$order->set_total( 50 ); // 4 x $10 simple helper product

			// Save and return ID.
			return $order->save();
		} catch ( \Exception $e ) {
			$order->delete( true );

			throw new \Exception( $e->getMessage() );
		}
	}

	public function add_line_item( $order, $args = [], $save = true ) {
		$order = $save ? \wc_get_order( $order ) : $order;

		if ( empty( $args['product'] ) ) {
			$product = \wc_get_product( $this->factory->product->createSimple() );
		} else {
			$product = \wc_get_product( $args['product'] );
		}

		if ( empty( $args['qty'] ) ) {
			$qty = rand( 1, 6 );
		} else {
			$qty = $args['qty'];
		}

		$item = new \WC_Order_Item_Product();
		$item->set_props(
			[
				'product'  => $product,
				'quantity' => $qty,
				'subtotal' => \wc_get_price_excluding_tax( $product, [ 'qty' => $qty ] ),
				'total'    => \wc_get_price_excluding_tax( $product, [ 'qty' => $qty ] ),
			]
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$item->set_meta_data( $args['meta_data'] );
		}

		$order->add_item( $item );

		if ( ! $save ) {
			return $order;
		}

		$order->save();
	}

	public function add_shipping_line( $order, $args = [], $save = true ) {
		$order = $save ? \wc_get_order( $order ) : $order;

		$this->factory->shipping_zone->createLegacyFlatRate();
		$item = new WC_Order_Item_Shipping();
		$item->set_props(
			array_merge(
				[
					'method_title' => 'Flat Rate',
					'method_id'    => 'flat_rate',
					'total'        => '',
					'total_tax'    => '',
					'taxes'        => [
						'total' => [],
					],
				],
				$args
			)
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$item->set_meta_data( $args['meta_data'] );
		}

		$item_id = $item->save();

		$order->add_item( $item );

		if ( ! $save ) {
			return $order;
		}

		$order->save();

		return $item_id;
	}

	public function add_coupon_line( $order, $coupon_id = 0, $save = true ) {
		$order = \wc_get_order( $order );

		// Create new coupon if $coupon_id not passed.
		if ( empty( $coupon_id ) ) {
			// Get order product IDs
			$product_ids = [];
			foreach ( $order->get_items() as $item ) {
				if ( ! in_array( $item->get_product_id(), $product_ids, true ) ) {
					$product_ids[] = $item->get_product_id();
				}
			}

			$coupon = new \WC_Coupon(
				$this->factory->coupon->create( [ 'product_ids' => $product_ids ] )
			);
		} else {
			$coupon = new \WC_Coupon( $coupon_id );
		}

		// Apply coupon to order.
		$order->apply_coupon( $coupon->get_code() );

		// If not saving return order.
		if ( ! $save ) {
			return $order;
		}

		// Save order.
		$order->save();
	}

	public function add_fee( $order, $args = [], $save = true ) {
		$order = \wc_get_order( $order );

		// Get thre customer country code.
		$country_code = $order->get_shipping_country();

		// Set the array for tax calculations.
		$calculate_tax_for = [
			'country'  => $country_code,
			'state'    => '',
			'postcode' => '',
			'city'     => '',
		];

		$imported_total_fee = 8.4342;

		// Create and add fee to order.
		$item = new \WC_Order_Item_Fee();
		$item->set_name( 'Fee' ); // Generic fee name
		$item->set_amount( $imported_total_fee ); // Fee amount
		$item->set_tax_class( '' ); // default for ''
		$item->set_tax_status( 'taxable' ); // or 'none'
		$item->set_total( $imported_total_fee ); // Fee amount

		if ( ! empty( $args ) ) {
			$item->set_props( $args );
		}

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$item->set_meta_data( $args['meta_data'] );
		}

		// Calculating Fee taxes
		$item->calculate_taxes( $calculate_tax_for );

		$order->add_item( $item );
		$order->calculate_totals();

		// If not saving return order.
		if ( ! $save ) {
			return $order;
		}

		// Save order.
		$order->save();
	}

	public function add_tax( $order, $args = [], $save = true ) {
		$order = \wc_get_order( $order );

		if ( empty( $args['rate_id'] ) ) {
			$rate_id = $this->factory->tax_rate->create();
		} else {
			$rate_id = $args['rate_id'];
		}

		$item = new \WC_Order_Item_Tax();
		$item->set_props(
			array_merge(
				[
					'rate_id'            => $rate_id,
					'tax_total'          => 100.66,
					'shipping_tax_total' => 150.45,
					'rate_code'          => \WC_Tax::get_rate_code( $rate_id ),
					'label'              => \WC_Tax::get_rate_label( $rate_id ),
					'compound'           => \WC_Tax::is_compound( $rate_id ),
				],
				$args
			)
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$item->set_meta_data( $args['meta_data'] );
		}

		$item->save();

		$order->add_item( $item );

		if ( ! $save ) {
			return $order;
		}

		$order->save();
	}

	public function set_to_customer_billing_address( $order, $customer, $save = true ) {
		$order = \wc_get_order( $order );
		if ( ! $customer ) {
			return $order;
		}
		$customer = new \WC_Customer( $customer );
		// Set billing address
		$order->set_billing_first_name( $customer->get_first_name() );
		$order->set_billing_last_name( $customer->get_last_name() );
		$order->set_billing_company( $customer->get_billing_company() );
		$order->set_billing_address_1( $customer->get_billing_address_1() );
		$order->set_billing_address_2( $customer->get_billing_address_2() );
		$order->set_billing_city( $customer->get_billing_city() );
		$order->set_billing_state( $customer->get_billing_state() );
		$order->set_billing_postcode( $customer->get_billing_postcode() );
		$order->set_billing_country( $customer->get_billing_country() );
		$order->set_billing_email( $customer->get_billing_email() );
		$order->set_billing_phone( $customer->get_billing_phone() );

		if ( $save ) {
			return $order->save();
		}

		return $order;
	}

	public function set_to_customer_shipping_address( $order, $customer, $save = true ) {
		$order = \wc_get_order( $order );
		if ( ! $customer ) {
			return $order;
		}
		$customer = new \WC_Customer( $customer );

		// Set shipping address
		$order->set_shipping_first_name( $customer->get_first_name() );
		$order->set_shipping_last_name( $customer->get_last_name() );
		$order->set_shipping_company( $customer->get_shipping_company() );
		$order->set_shipping_address_1( $customer->get_shipping_address_1() );
		$order->set_shipping_address_2( $customer->get_shipping_address_2() );
		$order->set_shipping_city( $customer->get_shipping_city() );
		$order->set_shipping_state( $customer->get_shipping_state() );
		$order->set_shipping_postcode( $customer->get_shipping_postcode() );
		$order->set_shipping_country( $customer->get_shipping_country() );

		if ( $save ) {
			return $order->save();
		}

		return $order;
	}

	public function delete_order( $id ) {
		$object = $this->get_object_by_id( $id );

		if ( is_a( $object, \WC_Order::class ) ) {
			$object->delete( true );
			return true;
		}

		return false;
	}

	public function get_order_key( $id ) {
		$object = $this->get_object_by_id( $id );
		return $object->get_order_key();
	}
}
