<?php

class OrderQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	private function create_customer( $username = 'testcustomer', $password = 'hunter2', $email = 'test@woo.local' ) {
		$customer = new WC_Customer();
		$customer->set_billing_country( 'US' );
		$customer->set_first_name( 'Justin' );
		$customer->set_billing_state( 'PA' );
		$customer->set_billing_postcode( '19123' );
		$customer->set_billing_city( 'Philadelphia' );
		$customer->set_billing_address( '123 South Street' );
		$customer->set_billing_address_2( 'Apt 1' );
		$customer->set_shipping_country( 'US' );
		$customer->set_shipping_state( 'PA' );
		$customer->set_shipping_postcode( '19123' );
		$customer->set_shipping_city( 'Philadelphia' );
		$customer->set_shipping_address( '123 South Street' );
		$customer->set_shipping_address_2( 'Apt 1' );
		$customer->set_username( $username );
		$customer->set_password( $password );
		$customer->set_email( $email );
		$customer->save();
		return $customer;
	}

	private function create_simple_product( $save = true ) {
		$product = new WC_Product_Simple();
		$product->set_props(
			array(
				'name'          => 'Dummy Product',
				'regular_price' => 10,
				'price'         => 10,
				'sku'           => 'DUMMY SKU',
				'manage_stock'  => false,
				'tax_status'    => 'taxable',
				'downloadable'  => false,
				'virtual'       => false,
				'stock_status'  => 'instock',
				'weight'        => '1.1',
			)
		);
		if ( $save ) {
			$product->save();
			return wc_get_product( $product->get_id() );
		} else {
			return $product;
		}
	}

	private function create_simple_flat_rate() {
		$flat_rate_settings = array(
			'enabled'      => 'yes',
			'title'        => 'Flat rate',
			'availability' => 'all',
			'countries'    => '',
			'tax_status'   => 'taxable',
			'cost'         => '10',
		);
		update_option( 'woocommerce_flat_rate_settings', $flat_rate_settings );
		update_option( 'woocommerce_flat_rate', array() );
		WC_Cache_Helper::get_transient_version( 'shipping', true );
		WC()->shipping()->load_shipping_methods();
	}

	private function create_order( $customer_id = 1, $product = null ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = $this->create_simple_product();
		}
		$this->create_simple_flat_rate();
		$order_data = array(
			'status'        => 'pending',
			'customer_id'   => $customer_id,
			'customer_note' => '',
			'total'         => '',
		);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // Required, else wc_create_order throws an exception
		$order 					= wc_create_order( $order_data );
		// Add order products
		$item = new WC_Order_Item_Product();
		$item->set_props( array(
			'product'  => $product,
			'quantity' => 4,
			'subtotal' => wc_get_price_excluding_tax( $product, array( 'qty' => 4 ) ),
			'total'    => wc_get_price_excluding_tax( $product, array( 'qty' => 4 ) ),
		) );
		$item->save();
		$order->add_item( $item );
		// Set billing address
		$order->set_billing_first_name( 'Jeroen' );
		$order->set_billing_last_name( 'Sormani' );
		$order->set_billing_company( 'WooCompany' );
		$order->set_billing_address_1( 'WooAddress' );
		$order->set_billing_address_2( '' );
		$order->set_billing_city( 'WooCity' );
		$order->set_billing_state( 'NY' );
		$order->set_billing_postcode( '123456' );
		$order->set_billing_country( 'US' );
		$order->set_billing_email( 'admin@example.org' );
		$order->set_billing_phone( '555-32123' );
		// Add shipping costs
		$shipping_taxes = WC_Tax::calc_shipping_tax( '10', WC_Tax::get_shipping_tax_rates() );
		$rate   = new WC_Shipping_Rate( 'flat_rate_shipping', 'Flat rate shipping', '10', $shipping_taxes, 'flat_rate' );
		$item   = new WC_Order_Item_Shipping();
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
		$order->save();
		return $order;
	}

	public function testOrderQuery() {
		$customer = $this->create_customer();
		$order = $this->create_order( $customer );
		$order_id = $order->get_id();

		$query = "
			query {
				orderBy(orderId: \"$order_id\") {
					orderId
					currency
					orderVersion
					date
					modified
					status
					discountTotal
					discountTax
					shippingTotal
					shippingTax
					cartTax
					total
					totalTax
					subtotal
					orderNumber
					orderKey
					createdVia
					pricesIncludeTax
					parent {
						id
					}
					customer {
						id
					}
					customerIpAddress
					customerUserAgent
					customerNote
					billing {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
						email
						phone
					}
					shipping {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
					}
					paymentMethod
					paymentMethodTitle
					transactionId
					customerIpAddress
					customerUserAgent
					customerNote
					dateCompleted
					datePaid
					cartHash
					shippingAddressMapUrl
					hasBillingAddress
					hasShippingAddress
					isDownloadPermitted
					needsShippingAddress
					hasDownloadableItem
					downloadableItems {
						nodes {
							id
						}
					}
					needsPayment
					needsProcessing
					items {
						nodes {
							id
						}
					}
					tax_lines {
						nodes {
							id
						}
					}
					shippingLines{
						nodes {
							id
						}
					}
					feeLines {
						nodes {
							id
						}
					}
					couponLines {
						nodes {
							id
						}
					}
					refunds {
						nodes {
							id
							reason
							total
						}
					}
				}
			}
		";

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}
}
