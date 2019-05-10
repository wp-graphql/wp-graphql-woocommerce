<?php

use GraphQLRelay\Relay;

class CartHelper extends WCG_Helper {
	public function to_relay_id( $id ) {
		return null;
	}

	public function print_query( $id = 0 ) {
		$cart = WC()->cart;
		return array(
			'subtotal'                => floatval( $cart->get_subtotal() ),
			'subtotalTax'             => floatval( $cart->get_subtotal_tax() ),
			'discountTotal'           => floatval( $cart->get_discount_total() ),
			'discountTax'             => floatval( $cart->get_discount_tax() ),
			'shippingTotal'           => floatval( $cart->get_shipping_total() ),
			'shippingTax'             => floatval( $cart->get_shipping_tax() ),
			'contentsTotal'           => floatval( $cart->get_cart_contents_total() ),
			'contentsTax'             => floatval( $cart->get_cart_contents_tax() ),
			'feeTotal'                => floatval( $cart->get_fee_total() ),
			'feeTax'                  => floatval( $cart->get_fee_tax() ),
			'total'                   => floatval( $cart->get_totals()['total'] ),
			'totalTax'                => floatval( $cart->get_total_tax() ),
			'isEmpty'                 => $cart->is_empty(),
			'displayPricesIncludeTax' => $cart->display_prices_including_tax(),
			'needsShippingAddress'    => $cart->needs_shipping_address(),
		);
	}

	public function print_item_query( $key ) {
		$cart = WC()->cart;
		$item = $cart->get_cart_item( $key );
		return array(
			'key'         => $item['key'],
			'product'     => array( 
				'id'        => Relay::toGlobalId( 'product', $item['product_id'] ),
				'productId' => $item['product_id'],
			),
			'variation'   => array(
				'id'          => Relay::toGlobalId( 'product_variation', $item['variation_id'] ),
				'variationId' => $item['variation_id']
			),
			'quantity'    => $item['quantity'],
			'subtotal'    => $item['line_subtotal'],
			'subtotalTax' => $item['line_subtotal_tax'],
			'total'       => $item['line_total'],
			'tax'         => $item['line_tax'],
		);
	}

	public function print_fee_query( $id ) {
		$cart = WC()->cart;
		$fees = $cart->get_fees();
		$fee  = ! empty( $fees[ $id ] ) ? $fees[ $id ] : null;

		return !empty( $fee ) 
			? array(
				'id'       => $fee->id,
				'name'     => $fee->name,
				'taxable'  => $fee->taxable,
				'taxClass' => $fee->tax_class,
				'amount'   => $fee->amount,
				'total'    => $fee->total,
			)
			: null;
	}

	public function print_nodes( $processors = array(), $_ = null ) {
		$cart = WC()->cart;
		$ids = array_keys( $cart->get_cart_contents() );
		$default_processors = array(
			'mapper' => function( $key ) {
				return array( 'key' => $key ); 
			},
			'sorter' => function( $key_a, $key_b ) {
				return strcmp( $key_a, $key_b );
			},
			'filter' => function( $key ) {
				return true;
			}
		);

		$processors = array_merge( $default_processors, $processors );

		$results = array_filter( $ids, $processors['filter'] );
		if( ! empty( $results ) ) {
			usort( $results, $processors['sorter'] );
		}

		return array_values( array_map( $processors['mapper'], $results ) );
	}

	public function print_fee_nodes( $processors = array(), $_ = null ) {
		$cart = WC()->cart;
		$ids = array_keys( $cart->get_fees() );
		$default_processors = array(
			'mapper' => function( $id ) {
				return array( 'id' => $id ); 
			},
			'sorter' => function( $id_a, $id_b ) {
				return 0;
			},
			'filter' => function( $id ) {
				return true;
			}
		);

		$processors = array_merge( $default_processors, $processors );

		$results = array_filter( $ids, $processors['filter'] );
		if( ! empty( $results ) ) {
			usort( $results, $processors['sorter'] );
		}

		return array_values( array_map( $processors['mapper'], $results ) );
	}
}