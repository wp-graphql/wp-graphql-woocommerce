<?php

use GraphQLRelay\Relay;

class CartHelper extends WCG_Helper {
	public function to_relay_id( $id ) {
		return null;
	}

	public function add( ...$products ) {
		$keys = array();

		foreach( $products as $product ) {
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
					! empty( $product['variation'] ) ? $product['variation'] : array(),
					! empty( $product['cart_item_data'] ) ? $product['cart_item_data'] : array()
				);
			} else {
				WC()->cart->add_to_cart( $product, 1 );
			}
		}

		return $keys;
	}

	public function print_query( $id = 0 ) {
		$cart = WC()->cart;
		return array(
			'subtotal'                => \wc_graphql_price( $cart->get_subtotal() ),
			'subtotalTax'             => \wc_graphql_price( $cart->get_subtotal_tax() ),
			'discountTotal'           => \wc_graphql_price( $cart->get_discount_total() ),
			'discountTax'             => \wc_graphql_price( $cart->get_discount_tax() ),
			'shippingTotal'           => \wc_graphql_price( $cart->get_shipping_total() ),
			'shippingTax'             => \wc_graphql_price( $cart->get_shipping_tax() ),
			'contentsTotal'           => \wc_graphql_price( $cart->get_cart_contents_total() ),
			'contentsTax'             => \wc_graphql_price( $cart->get_cart_contents_tax() ),
			'feeTotal'                => \wc_graphql_price( $cart->get_fee_total() ),
			'feeTax'                  => \wc_graphql_price( $cart->get_fee_tax() ),
			'total'                   => \wc_graphql_price( $cart->get_totals()['total'] ),
			'totalTax'                => \wc_graphql_price( $cart->get_total_tax() ),
			'isEmpty'                 => $cart->is_empty(),
			'displayPricesIncludeTax' => $cart->display_prices_including_tax(),
			'needsShippingAddress'    => $cart->needs_shipping_address(),
			'totalTaxes'			  => ! empty( $cart->get_tax_totals() )
				? array_map(
					function( $tax_data ) {
						return array(
							'id'         => $tax_data->tax_rate_id,
							'label'      => $tax_data->label,
							'isCompound' => $tax_data->is_compound,
							'amount'     => \wc_graphql_price( $tax_data->amount ),
						);
					},
					array_values( $cart->get_tax_totals() )
				)
				: null,
		);
	}

	public function print_item_query( $key ) {
		$cart = WC()->cart;
		$item = $cart->get_cart_item( $key );

		$variation      = $item['variation_id'];
		$variation_data = $item['variation'];

		// Stub any variation attributes for later use.
		$attributes = array();
		if ( ! empty( $variation ) ) {
			$variation = \wc_get_product( $variation );
			foreach ( $variation->get_attributes() as $name => $default_value ) {
				if ( isset( $variation_data["attribute_{$name}"] ) ) {
					$value = $variation_data["attribute_{$name}"];
				} else {
					$value = $default_value;
				}

				$attribute = array(
					'id'    => base64_encode( $variation->get_id() . '||' . $name . '||' . $value ),
					'label' => ucwords( str_replace( '_', ' ', \wc_attribute_taxonomy_slug( $name ) ) ),
				);

				$term = \get_term_by( 'slug', $value, $name );
				if ( empty( $term ) ) {
					$attribute = array_merge(
						$attribute,
						array(
							'attributeId' => 0,
							'name'        => $name,
							'value'       => $value,
						)
					);
				} else {
					$attribute = array_merge(
						$attribute,
						array(
							'attributeId' => $term->term_id,
							'name'        => $term->taxonomy,
							'value'       => $term->name,
						)
					);
				}

				$attributes[] = $attribute;
			}
		}

		return array(
			'key'         => $item['key'],
			'product'     => array(
				'node' => array(
					'id'         => Relay::toGlobalId( 'product', $item['product_id'] ),
					'databaseId' => $item['product_id'],
				),
			),
			'variation'   => ! empty( $variation )
				? array(
					'attributes' => $attributes,
					'node'       => array(
						'id'         => Relay::toGlobalId( 'product_variation', $item['variation_id'] ),
						'databaseId' => $item['variation_id'],
					),
				)
				: null,
			'quantity'    => $item['quantity'],
			'subtotal'    => \wc_graphql_price( $item['line_subtotal'] ),
			'subtotalTax' => \wc_graphql_price( $item['line_subtotal_tax'] ),
			'total'       => \wc_graphql_price( $item['line_total'] ),
			'tax'         => \wc_graphql_price( $item['line_tax'] ),
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
		$ids = array_keys( $cart->get_cart() );
		$default_processors = array(
			'mapper' => function( $key ) {
				return array( 'key' => $key );
			},
			'filter' => function( $key ) {
				return true;
			}
		);

		$processors = array_merge( $default_processors, $processors );

		$results = array_filter( $ids, $processors['filter'] );

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
