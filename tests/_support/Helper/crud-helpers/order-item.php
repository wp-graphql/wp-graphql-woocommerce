<?php

use GraphQLRelay\Relay;

class OrderItemHelper extends WCG_Helper {
	public function __construct() {
		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return null;
	}

	public function add_coupon( $order, $coupon_id = 0, $save = true ) {
		// Retrieve order.
		if ( ! is_a( $order, WC_Order::class ) ) {
			$order = new WC_Order( $order );
		}

		// Create new coupon if $coupon_id not passed.
		if ( empty( $coupon_id ) ) {
			// Get order product IDs
			$product_ids = array();
			foreach( $order->get_items() as $item ) {
				if ( ! in_array( $item->get_product_id(), $product_ids ) ) {
					$product_ids[] = $item->get_product_id();
				}
			}

			$coupon = new WC_Coupon(
				CouponHelper::instance()->create( array( 'product_ids' => $product_ids ) )
			);
		} else {
			$coupon = new WC_Coupon( $coupon_id ); 
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

	public function add_fee( $order, $args = array(), $save = true ) {
		// Retrieve order.
		$order = new WC_Order( $order );

		// Get thre customer country code.
		$country_code = $order->get_shipping_country();

		// Set the array for tax calculations.
		$calculate_tax_for = array(
			'country' => $country_code, 
			'state' => '', 
			'postcode' => '', 
			'city' => ''
		);

		$imported_total_fee = 8.4342;

		// Create and add fee to order.
		$item = new WC_Order_Item_Fee();
		$item->set_name( "Fee" ); // Generic fee name
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

	public function add_shipping( $order, $args = array(), $save = true ) {
		if ( ! is_a( $order, WC_Order::class ) ) {
			$order = new WC_Order( $order );
		}

		ShippingMethodHelper::create_legacy_flat_rate_instance();
		$item = new WC_Order_Item_Shipping();
		$item->set_props(
			array_merge(
				array(
					'method_title' => 'Flat Rate',
					'method_id'    => 'flat_rate',
					'total'        => '',
					'total_tax'    => '',
					'taxes'        => array(
						'total' => array(),
					),
				),
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

	public function add_tax( $order, $args = array(), $save = true ) {
		if ( ! is_a( $order, WC_Order::class ) ) {
			$order = new WC_Order( $order );
		}

		if ( empty( $args['rate_id'] ) ) {
			$rate_id = TaxRateHelper::instance()->create();
		} else {
			$rate_id = $args['rate_id'];
		}

		$item = new WC_Order_Item_Tax();
		$item->set_props(
			array_merge(
				array(
					'rate_id'            => $rate_id,
					'tax_total'          => 100.66,
					'shipping_tax_total' => 150.45,
					'rate_code'          => WC_Tax::get_rate_code( $rate_id ),
					'label'              => WC_Tax::get_rate_label( $rate_id ),
					'compound'           => WC_Tax::is_compound( $rate_id ),
				),
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

	public function add_line_item( $order, $args = array(), $save = true ) {
		if ( ! is_a( $order, WC_Order::class ) ) {
			$order = new WC_Order( $order );
		}

		if ( empty( $args['product'] ) ) {
			$product = wc_get_product( ProductHelper::instance()->create_simple() );
		} else {
			$product = wc_get_product( $args['product'] );
		}

		if ( empty( $args['qty'] ) ) {
			$qty = rand( 1, 6 );
		} else {
			$qty = $args['qty'];
		}

		$item = new WC_Order_Item_Product();
		$item->set_props(
			array(
				'product'  => $product,
				'quantity' => $qty,
				'subtotal' => wc_get_price_excluding_tax( $product, array( 'qty' => $qty ) ),
				'total'    => wc_get_price_excluding_tax( $product, array( 'qty' => $qty ) ),
			)
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

	public function print_query( $id ) {
		return null;
	}

	public function print_nodes( $ids = 0, $processors = array() ) {
		return array();
	}
}