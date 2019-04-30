<?php

use GraphQLRelay\Relay;

class OrderHelper extends WCG_Helper {
	public function __construct() {
		$this->node_type = 'shop_order';

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'shop_order', $id );
	}

    public function set_to_customer_billing_address( $order, $customer, $save = true ) {
        if ( ! is_a( $order, WC_Order::class ) ) {
            $order = new WC_Order( absint( $order ) );
        }
        if ( ! is_a( $customer, WC_Customer::class ) ) {
            $customer = new WC_Customer( $customer );
        }

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
        if ( ! is_a( $order, WC_Order::class ) ) {
            $order = new WC_Order( absint( $order ) );
        }
        if ( ! is_a( $customer, WC_Customer::class ) ) {
            $customer = new WC_Customer( $customer );
        }

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

	public function create( $args = array(), $items = array() ) {
		if ( empty( $args['customer_id'] ) ) {
			$customer = new WC_Customer( CustomerHelper::instance()->create() );
			$customer_id = $customer->get_id();
		} else {
			$customer_id = $args['customer_id'];
		}

        ShippingMethodHelper::create_legacy_flat_rate_instance();

        // Create order
		$order_data = array_merge(
            array(
                'status'        => 'pending',
                'customer_id'   => $customer_id,
                'customer_note' => '',
                'total'         => '',
            ),
            $args
		);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // Required, else wc_create_order throws an exception
        $order 					= wc_create_order( $order_data );
        
		// Add line items
		if ( ! empty( $items['line_items'] ) ) {
			foreach( $items['line_items'] as $item ) {
				$order = OrderItemHelper::instance()->add_line_item( $order, $item, false );
			}
		} else {
            for ( $i = 0; $i < rand( 1, 3 ); $i++ ) {
				$order = OrderItemHelper::instance()->add_line_item(
					$order,
					array(
						'product' => ProductHelper::instance()->create_simple(),
						'qty'     => rand( 1, 6 ),
					),
					false
				);
            }
		}
		$order->save();
		
        
        // Add billing / shipping address
        $order = $this->set_to_customer_billing_address( $order, $customer_id, false );
        $order = $this->set_to_customer_shipping_address( $order, $customer_id, false );
        
		// Add shipping costs
		$shipping_taxes = WC_Tax::calc_shipping_tax( '10', WC_Tax::get_shipping_tax_rates() );
		$rate           = new WC_Shipping_Rate( 'flat_rate_shipping', 'Flat rate shipping', '10', $shipping_taxes, 'flat_rate' );
		$item           = new WC_Order_Item_Shipping();
		$item->set_props( array(
			'method_title' => $rate->label,
			'method_id'    => $rate->id,
			'total'        => wc_format_decimal( $rate->cost ),
			'taxes'        => $rate->taxes,
		) );
		foreach ( $rate->get_meta_data() as $key => $value ) {
			$item->add_meta_data( $key, $value, true );
		}
        $order->add_item( $item );
        
		// Set payment gateway
		$payment_gateways = WC()->payment_gateways->payment_gateways();
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
	}

	public function get_order_key( $id ) {
		return (string) get_post_meta( $id, '_order_key', true );
	}

	public function has_product( $id, $product_id ) {
		$order = new WC_Order( $id );
		$line_items = $order->get_items(); 
        foreach ( $line_items as $item ) {
            if ( $item['product_id'] == $product_id ) {
                return true;
            }
		}
		return false;
	}

    public function print_query( $id ) {
		$data = new WC_Order( $id );

		if ( ! $data ) {
			return null;
		}

		return array(
			'id'                    => $this->to_relay_id( $id ),
			'orderId'               => $data->get_id(),
			'currency'              => ! empty( $data->get_currency() ) ? $data->get_currency() : null,
			'orderVersion'          => ! empty( $data->get_version() ) ? $data->get_version() : null,
            'date'                  => $data->get_date_created()->__toString(),
            'modified'              => $data->get_date_modified()->__toString(),
			'status'                => strtoupper( $data->get_status() ),
			'discountTotal'         => floatval( $data->get_discount_total() ),
			'discountTax'           => floatval( $data->get_discount_tax() ),
			'shippingTotal'         => floatval( $data->get_shipping_total() ),
			'shippingTax'           => floatval( $data->get_shipping_tax() ),
			'cartTax'               => floatval( $data->get_cart_tax() ),
			'total'                 => floatval( $data->get_total() ),
			'totalTax'              => floatval( $data->get_total_tax() ),
			'subtotal'              => floatval( $data->get_subtotal() ),
			'orderNumber'           => $data->get_order_number(),
			'orderKey'              => $data->get_order_key(),
			'createdVia'            => ! empty( $data->get_created_via() )
				? $data->get_created_via()
				: null,
			'pricesIncludeTax'      => $data->get_prices_include_tax(),
			'parent'                => null,
			'customer'              => array(
				'id' =>	Relay::toGlobalId( 'customer', $data->get_customer_id() )
			),
			'customerIpAddress'     => ! empty( $data->get_customer_ip_address() )
				? $data->get_customer_ip_address()
				: null,
			'customerUserAgent'     => ! empty( $data->get_customer_user_agent() )
				? $data->get_customer_user_agent()
				: null,
			'customerNote'          => ! empty( $data->get_customer_note() )
				? $data->get_customer_note()
				: null,
			'billing'               => array(
				'firstName' => ! empty( $data->get_billing_first_name() )
					? $data->get_billing_first_name()
					: null,
				'lastName'  => ! empty( $data->get_billing_last_name() )
					? $data->get_billing_last_name()
					: null,
				'company'   => ! empty( $data->get_billing_company() )
					? $data->get_billing_company()
					: null,
				'address1'  => ! empty( $data->get_billing_address_1() )
					? $data->get_billing_address_1()
					: null,
				'address2'  => ! empty( $data->get_billing_address_2() )
					? $data->get_billing_address_2()
					: null,
				'city'      => ! empty( $data->get_billing_city() )
					? $data->get_billing_city()
					: null,
				'state'     => ! empty( $data->get_billing_state() )
					? $data->get_billing_state()
					: null,
				'postcode'  => ! empty( $data->get_billing_postcode() )
					? $data->get_billing_postcode()
					: null,
				'country'   => ! empty( $data->get_billing_country() )
					? $data->get_billing_country()
					: null,
				'email'     => ! empty( $data->get_billing_email() )
					? $data->get_billing_email()
					: null,
				'phone'     => ! empty( $data->get_billing_phone() )
					? $data->get_billing_phone()
					: null,
			),
			'shipping'              => array(
				'firstName' => ! empty( $data->get_shipping_first_name() )
					? $data->get_shipping_first_name()
					: null,
				'lastName'  => ! empty( $data->get_shipping_last_name() )
					? $data->get_shipping_last_name()
					: null,
				'company'   => ! empty( $data->get_shipping_company() )
					? $data->get_shipping_company()
					: null,
				'address1'  => ! empty( $data->get_shipping_address_1() )
					? $data->get_shipping_address_1()
					: null,
				'address2'  => ! empty( $data->get_shipping_address_2() )
					? $data->get_shipping_address_2()
					: null,
				'city'      => ! empty( $data->get_shipping_city() )
					? $data->get_shipping_city()
					: null,
				'state'     => ! empty( $data->get_shipping_state() )
					? $data->get_shipping_state()
					: null,
				'postcode'  => ! empty( $data->get_shipping_postcode() )
					? $data->get_shipping_postcode()
					: null,
				'country'   => ! empty( $data->get_shipping_country() )
					? $data->get_shipping_country()
					: null,
			),
			'paymentMethod'         => ! empty( $data->get_payment_method() )
				? $data->get_payment_method()
				: null,
			'paymentMethodTitle'    => ! empty( $data->get_payment_method_title() )
				? $data->get_payment_method_title()
				: null,
			'transactionId'         => ! empty( $data->get_transaction_id() )
				? $data->get_transaction_id()
				: null,
			'dateCompleted'         => ! empty( $data->get_date_completed() )
				? $data->get_date_completed()
				: null,
			'datePaid'              => ! empty( $data->get_date_paid() )
				? $data->get_date_paid()
				: null,
			'cartHash'              => ! empty( $data->get_cart_hash() )
				? $data->get_cart_hash()
				: null,
			'shippingAddressMapUrl' => ! empty( $data->get_shipping_address_map_url() )
				? $data->get_shipping_address_map_url()
				: null,
			'hasBillingAddress'     => $data->has_billing_address(),
			'hasShippingAddress'    => $data->has_shipping_address(),
			'isDownloadPermitted'   => $data->is_download_permitted(),
			'needsShippingAddress'  => $data->needs_shipping_address(),
			'hasDownloadableItem'   => $data->has_downloadable_item(),
			'downloadableItems'     => array_map(
				function( $download ) {
					return array( 'downloadId' => $download->get_id() );
				},
				$data->get_downloadable_items()
			),
			'needsPayment'          => $data->needs_payment(),
			'needsProcessing'       => $data->needs_processing(),
		);
	}
	
	public function print_restricted_query( $id ) {
		$data = new WC_Order( $id );

		if ( ! $data ) {
			return null;
		}

		return array(
			'id'                    => $this->to_relay_id( $id ),
			'orderId'               => $id,
			'currency'              => null,
			'orderVersion'          => null,
            'date'                  => null,
            'modified'              => null,
			'status'                => null,
			'discountTotal'         => null,
			'discountTax'           => null,
			'shippingTotal'         => null,
			'shippingTax'           => null,
			'cartTax'               => null,
			'total'                 => null,
			'totalTax'              => null,
			'subtotal'              => null,
			'orderNumber'           => null,
			'orderKey'              => null,
			'createdVia'            => null,
			'pricesIncludeTax'      => null,
			'parent'                => null,
			'customer'              => null,
			'customerIpAddress'     => null,
			'customerUserAgent'     => null,
			'customerNote'          => null,
			'billing'               => null,
			'shipping'              => null,
			'paymentMethod'         => null,
			'paymentMethodTitle'    => null,
			'transactionId'         => null,
			'dateCompleted'         => null,
			'datePaid'              => null,
			'cartHash'              => null,
			'shippingAddressMapUrl' => null,
			'hasBillingAddress'     => null,
			'hasShippingAddress'    => null,
			'isDownloadPermitted'   => null,
			'needsShippingAddress'  => null,
			'hasDownloadableItem'   => null,
			'downloadableItems'     => null,
			'needsPayment'          => null,
			'needsProcessing'       => null,
		);
	}
}