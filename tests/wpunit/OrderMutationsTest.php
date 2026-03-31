<?php

use WPGraphQL\Type\WPEnumType;

class OrderMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		// Turn on tax calculations. Important!
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

		// Enable stock management.
		update_option( 'woocommerce_manage_stock', 'yes' );

		$gateways                          = \WC()->payment_gateways->payment_gateways();
		$bacs_gateway                      = $gateways['bacs'];
		$bacs_gateway->settings['enabled'] = 'yes';
		update_option( $bacs_gateway->get_option_key(), $bacs_gateway->settings );

		$cod_gateway                      = $gateways['cod'];
		$cod_gateway->settings['enabled'] = 'yes';
		update_option( $cod_gateway->get_option_key(), $cod_gateway->settings );
		
		\WC()->payment_gateways->init();

		// Create a tax rate.
		$this->factory->tax_rate->create(
			[
				'country'  => '',
				'state'    => '',
				'rate'     => 20.000,
				'name'     => 'VAT',
				'priority' => '1',
				'compound' => '0',
				'shipping' => '1',
				'class'    => '',
			]
		);
		// Create sample order to be used as a parent order.
	}

	private function orderMutation( $input, $operation_name = 'createOrder', $input_type = 'CreateOrderInput' ) {
		$mutation = "
            mutation {$operation_name}( \$input: {$input_type}! ) {
                {$operation_name}( input: \$input ) {
                    clientMutationId
                    order {
                        id
                        databaseId
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
                                url
                                accessExpires
                                downloadId
                                downloadsRemaining
                                name
                                product {
                                    databaseId
                                }
                            }
                        }
                        needsPayment
                        needsProcessing
                        metaData {
                            key
                            value
                        }
                        couponLines {
                            nodes {
                                databaseId
                                orderId
                                code
                                discount
                                discountTax
                                coupon {
                                    id
                                }
                            }
                        }
                        feeLines {
                            nodes {
                                databaseId
                                orderId
                                amount
                                name
                                taxStatus
                                total
                                totalTax
                                taxClass
                            }
                        }
                        shippingLines {
                            nodes {
                                databaseId
                                orderId
                                methodTitle
                                total
                                totalTax
                                taxClass
                            }
                        }
                        taxLines {
                            nodes {
                                rateCode
                                label
                                taxTotal
                                shippingTaxTotal
                                isCompound
                                taxRate {
                                    databaseId
                                }
                            }
                        }
                        lineItems {
                            nodes {
                                productId
                                variationId
                                quantity
                                taxClass
                                subtotal
                                subtotalTax
                                total
                                totalTax
                                taxStatus
                                product {
                                    node {
                                        id
                                    }
                                }
                                variation {
                                    node {
                                        id
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ";

		return $this->graphql(
			[
				'query'          => $mutation,
				'operation_name' => $operation_name,
				'variables'      => [ 'input' => $input ],
			]
		);
	}

	// tests
	public function testCreateOrderMutation() {
		$variable    = $this->factory->product_variation->createSome( $this->factory->product->createVariable() );
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create( [ 'product_ids' => $product_ids ] )
		);

		$input = [
			'clientMutationId'   => 'someId',
			'customerId'         => $this->customer,
			'customerNote'       => 'Customer test note',
			'coupons'            => [
				$coupon->get_code(),
			],
			'paymentMethod'      => 'bacs',
			'paymentMethodTitle' => 'Direct Bank Transfer',
			'billing'            => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
				'email'     => 'superfreak500@gmail.com',
				'phone'     => '555-555-1234',
			],
			'shipping'           => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
			],
			'lineItems'          => [
				[
					'productId' => $product_ids[0],
					'quantity'  => 5,
					'metaData'  => [
						[
							'key'   => 'test_product_key',
							'value' => 'test product value',
						],
					],
				],
				[
					'productId' => $product_ids[1],
					'quantity'  => 2,
				],
				[
					'productId'   => $product_ids[2],
					'quantity'    => 6,
					'variationId' => $variable['variations'][0],
				],
			],
			'shippingLines'      => [
				[
					'methodId'    => 'flat_rate_shipping',
					'methodTitle' => 'Flat Rate shipping',
					'total'       => '10',
				],
			],
			'feeLines'           => [
				[
					'name'      => 'Some Fee',
					'taxStatus' => 'TAXABLE',
					'total'     => '100',
					'taxClass'  => 'STANDARD',
				],
			],
			'metaData'           => [
				[
					'key'   => 'test_key',
					'value' => 'test value',
				],
			],
			'currency' 		     => 'VND',
			'isPaid'             => true,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot create order an order.
		 */
		$this->loginAsCustomer();
		$response = $this->orderMutation( $input );


		$this->assertQueryError( $response );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$response = $this->orderMutation( $input );


		$this->assertArrayHasKey( 'data', $response );
		$this->assertArrayHasKey( 'createOrder', $response['data'] );
		$this->assertArrayHasKey( 'order', $response['data']['createOrder'] );
		$this->assertArrayHasKey( 'id', $response['data']['createOrder']['order'] );
		$order = \WC_Order_Factory::get_order( $response['data']['createOrder']['order']['databaseId'] );

		$expected = [
			$this->expectedField( 'createOrder.clientMutationId', 'someId' ),
			$this->expectedField( 'createOrder.order.id', $this->toRelayId( 'order', $order->get_id() ) ),
			$this->expectedField( 'createOrder.order.databaseId', $order->get_id() ),
			$this->expectedField( 'createOrder.order.currency', self::NOT_NULL ),
			$this->expectedField( 'createOrder.order.status', self::NOT_NULL ),
			$this->expectedField( 'createOrder.order.customerNote', 'Customer test note' ),
			$this->expectedField( 'createOrder.order.billing.firstName', 'May' ),
			$this->expectedField( 'createOrder.order.billing.lastName', 'Parker' ),
			$this->expectedField( 'createOrder.order.billing.address1', '20 Ingram St' ),
			$this->expectedField( 'createOrder.order.billing.city', 'New York City' ),
			$this->expectedField( 'createOrder.order.billing.state', 'NY' ),
			$this->expectedField( 'createOrder.order.billing.postcode', '12345' ),
			$this->expectedField( 'createOrder.order.billing.country', 'US' ),
			$this->expectedField( 'createOrder.order.billing.email', 'superfreak500@gmail.com' ),
			$this->expectedField( 'createOrder.order.billing.phone', '555-555-1234' ),
			$this->expectedField( 'createOrder.order.shipping.firstName', 'May' ),
			$this->expectedField( 'createOrder.order.shipping.lastName', 'Parker' ),
			$this->expectedField( 'createOrder.order.shipping.address1', '20 Ingram St' ),
			$this->expectedField( 'createOrder.order.shipping.city', 'New York City' ),
			$this->expectedField( 'createOrder.order.shipping.state', 'NY' ),
			$this->expectedField( 'createOrder.order.shipping.postcode', '12345' ),
			$this->expectedField( 'createOrder.order.shipping.country', 'US' ),
			$this->expectedField( 'createOrder.order.paymentMethod', 'bacs' ),
			$this->expectedField( 'createOrder.order.paymentMethodTitle', 'Direct Bank Transfer' ),
		];

		// Validate coupon lines.
		$coupon_items = array_values( $order->get_items( 'coupon' ) );
		foreach ( $coupon_items as $i => $item ) {
			$expected[] = $this->expectedField( "createOrder.order.couponLines.nodes.{$i}.databaseId", $item->get_id() );
			$expected[] = $this->expectedField( "createOrder.order.couponLines.nodes.{$i}.orderId", $item->get_order_id() );
			$expected[] = $this->expectedField( "createOrder.order.couponLines.nodes.{$i}.code", $item->get_code() );
			$expected[] = $this->expectedField(
				"createOrder.order.couponLines.nodes.{$i}.coupon.id",
				$this->toRelayId( 'shop_coupon', \wc_get_coupon_id_by_code( $item->get_code() ) )
			);
		}

		// Validate fee lines.
		$fee_items = array_values( $order->get_items( 'fee' ) );
		foreach ( $fee_items as $i => $item ) {
			$expected[] = $this->expectedField( "createOrder.order.feeLines.nodes.{$i}.databaseId", $item->get_id() );
			$expected[] = $this->expectedField( "createOrder.order.feeLines.nodes.{$i}.name", $item->get_name() );
			$expected[] = $this->expectedField( "createOrder.order.feeLines.nodes.{$i}.taxStatus", strtoupper( $item->get_tax_status() ) );
			$expected[] = $this->expectedField( "createOrder.order.feeLines.nodes.{$i}.total", $item->get_total() );
		}

		// Validate shipping lines.
		$shipping_items = array_values( $order->get_items( 'shipping' ) );
		foreach ( $shipping_items as $i => $item ) {
			$expected[] = $this->expectedField( "createOrder.order.shippingLines.nodes.{$i}.databaseId", $item->get_id() );
			$expected[] = $this->expectedField( "createOrder.order.shippingLines.nodes.{$i}.methodTitle", $item->get_method_title() );
			$expected[] = $this->expectedField( "createOrder.order.shippingLines.nodes.{$i}.total", $item->get_total() );
		}

		// Validate tax lines.
		$tax_items = array_values( $order->get_items( 'tax' ) );
		foreach ( $tax_items as $i => $item ) {
			$expected[] = $this->expectedField( "createOrder.order.taxLines.nodes.{$i}.rateCode", $item->get_rate_code() );
			$expected[] = $this->expectedField( "createOrder.order.taxLines.nodes.{$i}.label", $item->get_label() );
			$expected[] = $this->expectedField( "createOrder.order.taxLines.nodes.{$i}.taxRate.databaseId", $item->get_rate_id() );
		}

		// Validate line items.
		$line_items = array_values( $order->get_items() );
		foreach ( $line_items as $i => $item ) {
			$expected[] = $this->expectedField( "createOrder.order.lineItems.nodes.{$i}.productId", $item->get_product_id() );
			$expected[] = $this->expectedField( "createOrder.order.lineItems.nodes.{$i}.quantity", $item->get_quantity() );
			$expected[] = $this->expectedField( "createOrder.order.lineItems.nodes.{$i}.taxStatus", strtoupper( $item->get_tax_status() ) );
			$expected[] = $this->expectedField(
				"createOrder.order.lineItems.nodes.{$i}.product.node.id",
				$this->toRelayId( 'post', $item->get_product_id() )
			);
			if ( ! empty( $item->get_variation_id() ) ) {
				$expected[] = $this->expectedField( "createOrder.order.lineItems.nodes.{$i}.variationId", $item->get_variation_id() );
				$expected[] = $this->expectedField(
					"createOrder.order.lineItems.nodes.{$i}.variation.node.id",
					$this->toRelayId( 'post', $item->get_variation_id() )
				);
			}
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateOrderMutation() {
		// Create products and coupons to be used in order creation.
		$variable    = $this->factory->product_variation->createSome( $this->factory->product->createVariable() );
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create( [ 'product_ids' => $product_ids ] )
		);

		// Create initial order input.
		$initial_input = [
			'clientMutationId'   => 'someId',
			'customerId'         => $this->customer,
			'customerNote'       => 'Customer test note',
			'coupons'            => [
				$coupon->get_code(),
			],
			'paymentMethod'      => 'bacs',
			'paymentMethodTitle' => 'Direct Bank Transfer',
			'billing'            => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
				'email'     => 'superfreak500@gmail.com',
				'phone'     => '555-555-1234',
			],
			'shipping'           => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
			],
			'lineItems'          => [
				[
					'productId' => $product_ids[0],
					'quantity'  => 5,
					'metaData'  => [
						[
							'key'   => 'test_product_key',
							'value' => 'test product value',
						],
					],
				],
				[
					'productId' => $product_ids[1],
					'quantity'  => 2,
				],
				[
					'productId'   => $product_ids[2],
					'quantity'    => 6,
					'variationId' => $variable['variations'][0],
				],
			],
			'shippingLines'      => [
				[
					'methodId'    => 'flat_rate_shipping',
					'methodTitle' => 'Flat Rate shipping',
					'total'       => '10',
				],
			],
			'feeLines'           => [
				[
					'name'      => 'Some Fee',
					'taxStatus' => 'TAXABLE',
					'total'     => '100',
					'taxClass'  => 'STANDARD',
				],
			],
			'metaData'           => [
				[
					'key'   => 'test_key',
					'value' => 'test value',
				],
			],
			'isPaid'             => false,
		];

		// Create order to update.
		$this->loginAsShopManager();
		$initial_response = $this->orderMutation( $initial_input );

		// use --debug flag to view.

		// Retrieve order and items
		$order          = \WC_Order_Factory::get_order( $initial_response['data']['createOrder']['order']['databaseId'] );
		$line_items     = $order->get_items();
		$shipping_lines = $order->get_items( 'shipping' );
		$fee_lines      = $order->get_items( 'fee' );

		// Create update order input.
		$updated_input = [
			'id'               => $this->toRelayId( 'order', $order->get_id() ),
			'clientMutationId' => 'someId',
			'customerNote'     => 'Customer test note',
			'coupons'          => [
				$coupon->get_code(),
			],
			'billing'          => [
				'firstName' => 'Ben',
			],
			'shipping'         => [
				'firstName' => 'Ben',
			],
			'lineItems'        => [
				[

					'id'       => array_keys( $line_items )[0],
					'quantity' => 6,
					'metaData' => [
						[
							'key'   => 'test_product_key',
							'value' => 'updated test product value',
						],
					],
				],
				[
					'id'       => array_keys( $line_items )[1],
					'quantity' => 1,
				],
				[
					'id'       => array_keys( $line_items )[2],
					'quantity' => 10,
				],
			],
			'shippingLines'    => [
				[
					'id'          => array_keys( $shipping_lines )[0],
					'methodId'    => 'reduced_rate_shipping',
					'methodTitle' => 'reduced Rate shipping',
					'total'       => '7',
				],
			],
			'feeLines'         => [
				[
					'id'        => array_keys( $fee_lines )[0],
					'name'      => 'Some Updated Fee',
					'taxStatus' => 'TAXABLE',
					'total'     => '125',
					'taxClass'  => 'STANDARD',
				],
			],
			'metaData'         => [
				[
					'key'   => 'test_key',
					'value' => 'new test value',
				],
			],
			'isPaid'           => true,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot update order an order.
		 */
		wp_set_current_user( $this->factory->user->create( [ 'role' => 'customer' ] ) );
		$response = $this->orderMutation(
			$updated_input,
			'updateOrder',
			'UpdateOrderInput'
		);


		$this->assertQueryError( $response );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$response = $this->orderMutation(
			$updated_input,
			'updateOrder',
			'UpdateOrderInput'
		);


		// Apply new changes to order instances.
		$order = \WC_Order_Factory::get_order( $order->get_id() );

		$expected = [
			$this->expectedField( 'updateOrder.clientMutationId', 'someId' ),
			$this->expectedField( 'updateOrder.order.id', $this->toRelayId( 'order', $order->get_id() ) ),
			$this->expectedField( 'updateOrder.order.databaseId', $order->get_id() ),
			$this->expectedField( 'updateOrder.order.currency', self::NOT_NULL ),
			$this->expectedField( 'updateOrder.order.status', self::NOT_NULL ),
			$this->expectedField( 'updateOrder.order.customerNote', 'Customer test note' ),
			$this->expectedField( 'updateOrder.order.billing.firstName', 'Ben' ),
			$this->expectedField( 'updateOrder.order.shipping.firstName', 'Ben' ),
		];

		// Validate coupon lines.
		$coupon_items = array_values( $order->get_items( 'coupon' ) );
		foreach ( $coupon_items as $i => $item ) {
			$expected[] = $this->expectedField( "updateOrder.order.couponLines.nodes.{$i}.databaseId", $item->get_id() );
			$expected[] = $this->expectedField( "updateOrder.order.couponLines.nodes.{$i}.code", $item->get_code() );
			$expected[] = $this->expectedField(
				"updateOrder.order.couponLines.nodes.{$i}.coupon.id",
				$this->toRelayId( 'shop_coupon', \wc_get_coupon_id_by_code( $item->get_code() ) )
			);
		}

		// Validate fee lines.
		$fee_items = array_values( $order->get_items( 'fee' ) );
		foreach ( $fee_items as $i => $item ) {
			$expected[] = $this->expectedField( "updateOrder.order.feeLines.nodes.{$i}.databaseId", $item->get_id() );
			$expected[] = $this->expectedField( "updateOrder.order.feeLines.nodes.{$i}.name", $item->get_name() );
			$expected[] = $this->expectedField( "updateOrder.order.feeLines.nodes.{$i}.total", $item->get_total() );
		}

		// Validate shipping lines.
		$shipping_items = array_values( $order->get_items( 'shipping' ) );
		foreach ( $shipping_items as $i => $item ) {
			$expected[] = $this->expectedField( "updateOrder.order.shippingLines.nodes.{$i}.databaseId", $item->get_id() );
			$expected[] = $this->expectedField( "updateOrder.order.shippingLines.nodes.{$i}.methodTitle", $item->get_method_title() );
			$expected[] = $this->expectedField( "updateOrder.order.shippingLines.nodes.{$i}.total", $item->get_total() );
		}

		// Validate tax lines.
		$tax_items = array_values( $order->get_items( 'tax' ) );
		foreach ( $tax_items as $i => $item ) {
			$expected[] = $this->expectedField( "updateOrder.order.taxLines.nodes.{$i}.rateCode", $item->get_rate_code() );
			$expected[] = $this->expectedField( "updateOrder.order.taxLines.nodes.{$i}.label", $item->get_label() );
			$expected[] = $this->expectedField( "updateOrder.order.taxLines.nodes.{$i}.taxRate.databaseId", $item->get_rate_id() );
		}

		// Validate line items.
		$updated_line_items = array_values( $order->get_items() );
		foreach ( $updated_line_items as $i => $item ) {
			$expected[] = $this->expectedField( "updateOrder.order.lineItems.nodes.{$i}.productId", $item->get_product_id() );
			$expected[] = $this->expectedField( "updateOrder.order.lineItems.nodes.{$i}.quantity", $item->get_quantity() );
			$expected[] = $this->expectedField( "updateOrder.order.lineItems.nodes.{$i}.taxStatus", strtoupper( $item->get_tax_status() ) );
			$expected[] = $this->expectedField(
				"updateOrder.order.lineItems.nodes.{$i}.product.node.id",
				$this->toRelayId( 'post', $item->get_product_id() )
			);
			if ( ! empty( $item->get_variation_id() ) ) {
				$expected[] = $this->expectedField( "updateOrder.order.lineItems.nodes.{$i}.variationId", $item->get_variation_id() );
				$expected[] = $this->expectedField(
					"updateOrder.order.lineItems.nodes.{$i}.variation.node.id",
					$this->toRelayId( 'post', $item->get_variation_id() )
				);
			}
		}

		$this->assertQuerySuccessful( $response, $expected );
		$this->assertNotEquals( $initial_response, $response );
	}

	public function testDeleteOrderMutation() {
		// Create products and coupons to be used in order creation.
		$variable    = $this->factory->product_variation->createSome( $this->factory->product->createVariable() );
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create( [ 'product_ids' => $product_ids ] )
		);

		// Create initial order input.
		$initial_input = [
			'clientMutationId'   => 'someId',
			'customerId'         => $this->customer,
			'customerNote'       => 'Customer test note',
			'coupons'            => [
				$coupon->get_code(),
			],
			'paymentMethod'      => 'bacs',
			'paymentMethodTitle' => 'Direct Bank Transfer',
			'billing'            => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
				'email'     => 'superfreak500@gmail.com',
				'phone'     => '555-555-1234',
			],
			'shipping'           => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
			],
			'lineItems'          => [
				[
					'productId' => $product_ids[0],
					'quantity'  => 5,
					'metaData'  => [
						[
							'key'   => 'test_product_key',
							'value' => 'test product value',
						],
					],
				],
				[
					'productId' => $product_ids[1],
					'quantity'  => 2,
				],
				[
					'productId'   => $product_ids[2],
					'quantity'    => 6,
					'variationId' => $variable['variations'][0],
				],
			],
			'shippingLines'      => [
				[
					'methodId'    => 'flat_rate_shipping',
					'methodTitle' => 'Flat Rate shipping',
					'total'       => '10',
				],
			],
			'feeLines'           => [
				[
					'name'      => 'Some Fee',
					'taxStatus' => 'TAXABLE',
					'total'     => '100',
					'taxClass'  => 'STANDARD',
				],
			],
			'metaData'           => [
				[
					'key'   => 'test_key',
					'value' => 'test value',
				],
			],
			'isPaid'             => false,
		];

		// Create order to delete.
		$this->loginAsShopManager();
		$initial_response = $this->orderMutation( $initial_input );

		// use --debug flag to view.

		// Clear loader cache.
		$this->getModule( '\Helper\Wpunit' )->clear_loader_cache( 'wc_post' );

		// Retrieve order and items
		$order_id       = $initial_response['data']['createOrder']['order']['databaseId'];
		$order          = \WC_Order_Factory::get_order( $order_id );
		$line_items     = $order->get_items();
		$shipping_lines = $order->get_items( 'shipping' );
		$fee_lines      = $order->get_items( 'fee' );
		$coupon_lines   = $order->get_items( 'coupon' );
		$tax_lines      = $order->get_items( 'tax' );

		// Create DeleteOrderInput.
		$deleted_input = [
			'clientMutationId' => 'someId',
			'id'               => $this->toRelayId( 'order', $order->get_id() ),
			'forceDelete'      => true,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot delete order an order.
		 */
		wp_set_current_user( $this->factory->user->create( [ 'role' => 'customer' ] ) );
		$response = $this->orderMutation(
			$deleted_input,
			'deleteOrder',
			'DeleteOrderInput'
		);


		$this->assertQueryError( $response );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$response = $this->orderMutation(
			$deleted_input,
			'deleteOrder',
			'DeleteOrderInput'
		);


		$expected = [
			$this->expectedField( 'deleteOrder.clientMutationId', 'someId' ),
			$this->expectedField( 'deleteOrder.order.id', $this->toRelayId( 'order', $order->get_id() ) ),
			$this->expectedField( 'deleteOrder.order.databaseId', $order->get_id() ),
		];

		$this->assertQuerySuccessful( $response, $expected );
		$this->assertFalse( \WC_Order_Factory::get_order( $order->get_id() ) );
	}

	public function testDeleteOrderItemsMutation() {
		// Create products and coupons to be used in order creation.
		$variable    = $this->factory->product_variation->createSome( $this->factory->product->createVariable() );
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create( [ 'product_ids' => $product_ids ] )
		);

		// Create initial order input.
		$initial_input = [
			'clientMutationId'   => 'someId',
			'customerId'         => $this->customer,
			'customerNote'       => 'Customer test note',
			'coupons'            => [
				$coupon->get_code(),
			],
			'paymentMethod'      => 'bacs',
			'paymentMethodTitle' => 'Direct Bank Transfer',
			'billing'            => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
				'email'     => 'superfreak500@gmail.com',
				'phone'     => '555-555-1234',
			],
			'shipping'           => [
				'firstName' => 'May',
				'lastName'  => 'Parker',
				'address1'  => '20 Ingram St',
				'city'      => 'New York City',
				'state'     => 'NY',
				'postcode'  => '12345',
				'country'   => 'US',
			],
			'lineItems'          => [
				[
					'productId' => $product_ids[0],
					'quantity'  => 5,
					'metaData'  => [
						[
							'key'   => 'test_product_key',
							'value' => 'test product value',
						],
					],
				],
				[
					'productId' => $product_ids[1],
					'quantity'  => 2,
				],
				[
					'productId'   => $product_ids[2],
					'quantity'    => 6,
					'variationId' => $variable['variations'][0],
				],
			],
			'shippingLines'      => [
				[
					'methodId'    => 'flat_rate_shipping',
					'methodTitle' => 'Flat Rate shipping',
					'total'       => '10',
				],
			],
			'feeLines'           => [
				[
					'name'      => 'Some Fee',
					'taxStatus' => 'TAXABLE',
					'total'     => '100',
					'taxClass'  => 'STANDARD',
				],
			],
			'metaData'           => [
				[
					'key'   => 'test_key',
					'value' => 'test value',
				],
			],
			'isPaid'             => false,
		];

		// Create order to delete.
		$this->loginAsShopManager();
		$initial_response = $this->orderMutation( $initial_input );

		// use --debug flag to view.

		// Clear loader cache.
		$this->getModule( '\Helper\Wpunit' )->clear_loader_cache( 'wc_post' );

		// Retrieve order and items
		$order_id       = $initial_response['data']['createOrder']['order']['databaseId'];
		$order          = \WC_Order_Factory::get_order( $order_id );
		$line_items     = $order->get_items();
		$shipping_lines = $order->get_items( 'shipping' );
		$fee_lines      = $order->get_items( 'fee' );
		$coupon_lines   = $order->get_items( 'coupon' );
		$tax_lines      = $order->get_items( 'tax' );

		// Create DeleteOrderInput.
		$deleted_items_input = [
			'clientMutationId' => 'someId',
			'orderId'          => $order->get_id(),
			'itemIds'          => [
				current( $line_items )->get_id(),
				current( $coupon_lines )->get_id(),
			],
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot delete order an order.
		 */
		wp_set_current_user( $this->factory->user->create( [ 'role' => 'customer' ] ) );
		$response = $this->orderMutation(
			$deleted_items_input,
			'deleteOrderItems',
			'DeleteOrderItemsInput'
		);


		$this->assertQueryError( $response );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$response = $this->orderMutation(
			$deleted_items_input,
			'deleteOrderItems',
			'DeleteOrderItemsInput'
		);


		$expected = [
			$this->expectedField( 'deleteOrderItems.clientMutationId', 'someId' ),
			$this->expectedField( 'deleteOrderItems.order.id', $this->toRelayId( 'order', $order->get_id() ) ),
			$this->expectedField( 'deleteOrderItems.order.databaseId', $order->get_id() ),
		];

		$this->assertQuerySuccessful( $response, $expected );
		$this->assertFalse( \WC_Order_Factory::get_order_item( current( $line_items ) ) );
		$this->assertFalse( \WC_Order_Factory::get_order_item( current( $coupon_lines ) ) );
	}

	private function orderNoteMutation( $input, $operation_name = 'createOrderNote', $input_type = 'CreateOrderNoteInput' ) {
		$mutation = "
			mutation {$operation_name}( \$input: {$input_type}! ) {
				{$operation_name}( input: \$input ) {
					clientMutationId
					orderNote {
						id
						databaseId
						dateCreated
						note
						isCustomerNote
					}
					order {
						id
						databaseId
					}
				}
			}
		";

		return $this->graphql(
			[
				'query'          => $mutation,
				'operation_name' => $operation_name,
				'variables'      => [ 'input' => $input ],
			]
		);
	}

	public function testCreateOrderNoteMutation() {
		$customer_id = $this->factory->customer->create();
		$order_id = $this->factory->order->createNew([
			'customer_id' => $customer_id,
		]);
		$input = [
			'clientMutationId' => 'someId',
			'orderId'          => $order_id,
			'note'             => 'Test order note content',
			'isCustomerNote'   => false,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot create an order note.
		 */
		$this->loginAsCustomer();
		$response = $this->orderNoteMutation( $input );

		$this->assertQueryError( $response );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$response = $this->orderNoteMutation( $input );

		$expected = [
			$this->expectedField( 'createOrderNote.clientMutationId', 'someId' ),
			$this->expectedField( 'createOrderNote.orderNote.note', 'Test order note content' ),
			$this->expectedField( 'createOrderNote.orderNote.isCustomerNote', false ),
			$this->expectedField( 'createOrderNote.orderNote.id', self::NOT_NULL ),
			$this->expectedField( 'createOrderNote.orderNote.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createOrderNote.orderNote.dateCreated', self::NOT_NULL ),
			$this->expectedField( 'createOrderNote.order.id', $this->toRelayId( 'order', $order_id ) ),
			$this->expectedField( 'createOrderNote.order.databaseId', $order_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Test customer note
		$customer_input = [
			'clientMutationId' => 'customerId',
			'orderId'          => $order_id,
			'note'             => 'Customer visible note',
			'isCustomerNote'   => true,
		];

		$customer_actual = $this->orderNoteMutation( $customer_input );

		$customer_expected = [
			$this->expectedField( 'createOrderNote.orderNote.note', 'Customer visible note' ),
			$this->expectedField( 'createOrderNote.orderNote.isCustomerNote', true ),
		];

		$this->assertQuerySuccessful( $customer_actual, $customer_expected );


		/**
		 * Assertion Three
		 *
		 * Test mutation and input
		 */
		$this->loginAs( $customer_id );
		$customer_input = [
			'clientMutationId' => 'customerId',
			'orderId'          => $order_id,
			'note'             => 'Customer visible note',
			'isCustomerNote'   => true,
		];

		$customer_actual = $this->orderNoteMutation( $customer_input );

		$customer_expected = [
			$this->expectedField( 'createOrderNote.orderNote.note', 'Customer visible note' ),
			$this->expectedField( 'createOrderNote.orderNote.isCustomerNote', true ),
		];

		$this->assertQuerySuccessful( $customer_actual, $customer_expected );
	}

	public function testDeleteOrderNoteMutation() {
		$order_id = $this->factory->order->createNew();

		// First create a note to delete
		$this->loginAsShopManager();
		$create_input = [
			'clientMutationId' => 'createId',
			'orderId'          => $order_id,
			'note'             => 'Note to be deleted',
			'isCustomerNote'   => false,
		];

		$create_result = $this->orderNoteMutation( $create_input );
		// Get note ID from the proper expected field result
		$note_id = $this->lodashGet( $create_result, 'data.createOrderNote.orderNote.databaseId' );

		$delete_input = [
			'clientMutationId' => 'deleteId',
			'id'               => $note_id,
			'orderId'          => $order_id,
			'force'            => true,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot delete an order note.
		 */
		$this->loginAsCustomer();
		$response = $this->orderNoteMutation( $delete_input, 'deleteOrderNote', 'DeleteOrderNoteInput' );

		$this->assertQueryError( $response );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$response = $this->orderNoteMutation( $delete_input, 'deleteOrderNote', 'DeleteOrderNoteInput' );

		$expected = [
			$this->expectedField( 'deleteOrderNote.clientMutationId', 'deleteId' ),
			$this->expectedField( 'deleteOrderNote.orderNote.note', 'Note to be deleted' ),
			$this->expectedField( 'deleteOrderNote.order.databaseId', $order_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Verify the note was deleted by checking it doesn't exist
		$deleted_note = get_comment( $note_id );
		$this->assertNull( $deleted_note );
	}

	public function testCreateOrderNoteValidation() {
		$order_id = $this->factory->order->createNew();

		$this->loginAsShopManager();

		// Test missing note content
		$invalid_input = [
			'clientMutationId' => 'invalidId',
			'orderId'          => $order_id,
			'note'             => '',
			'isCustomerNote'   => false,
		];

		$response = $this->orderNoteMutation( $invalid_input );
		$this->assertQueryError( $response );

		// Test invalid order ID
		$invalid_order_input = [
			'clientMutationId' => 'invalidOrderId',
			'orderId'          => 99999,
			'note'             => 'Valid note content',
			'isCustomerNote'   => false,
		];

		$response = $this->orderNoteMutation( $invalid_order_input );
		$this->assertQueryError( $response );
	}

	/**
	 * Test that updateOrder with only metaData does not create a duplicate order.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/591
	 */
	public function testUpdateOrderMetaDataDoesNotDuplicate() {
		$this->loginAsShopManager();

		// Create an order.
		$order_id = $this->factory->order->create(
			[
				'status'      => 'processing',
				'customer_id' => $this->customer,
			]
		);

		// Count orders before mutation.
		$orders_before = wc_get_orders( [ 'return' => 'ids', 'limit' => -1 ] );
		$count_before  = count( $orders_before );

		// Update only metaData — the exact scenario from #591.
		$mutation = '
			mutation updateOrder($input: UpdateOrderInput!) {
				updateOrder(input: $input) {
					order {
						databaseId
						metaData {
							key
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'id'       => $order_id,
				'metaData' => [
					[
						'key'   => '_tracking_number',
						'value' => 'ABC123',
					],
				],
			],
		];

		$response = $this->graphql(
			[
				'query'     => $mutation,
				'variables' => $variables,
			]
		);

		$expected = [
			$this->expectedField( 'updateOrder.order.databaseId', $order_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Verify metaData was set.
		$order = wc_get_order( $order_id );
		$this->assertEquals( 'ABC123', $order->get_meta( '_tracking_number' ) );

		// Count orders after mutation — should be the same.
		$orders_after = wc_get_orders( [ 'return' => 'ids', 'limit' => -1 ] );
		$count_after  = count( $orders_after );

		$this->assertEquals(
			$count_before,
			$count_after,
			'updateOrder with new metaData should not create a duplicate order.'
		);

		// Now update the same meta key with a new value.
		$count_before_update = $count_after;

		$variables['input']['metaData'] = [
			[
				'key'   => '_tracking_number',
				'value' => 'XYZ789',
			],
		];

		$response = $this->graphql(
			[
				'query'     => $mutation,
				'variables' => $variables,
			]
		);

		$this->assertQuerySuccessful( $response, $expected );

		// Verify metaData was updated.
		$order = wc_get_order( $order_id );
		$this->assertEquals( 'XYZ789', $order->get_meta( '_tracking_number' ) );

		// Count orders after updating existing meta — should still be the same.
		$orders_after_update = wc_get_orders( [ 'return' => 'ids', 'limit' => -1 ] );
		$count_after_update  = count( $orders_after_update );

		$this->assertEquals(
			$count_before_update,
			$count_after_update,
			'updateOrder with existing metaData should not create a duplicate order.'
		);
	}

	/**
	 * Test that createOrder with isPaid reduces stock quantity.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/313
	 */
	public function testCreateOrderReducesStockQuantity() {
		$this->loginAsShopManager();

		// Create a product with managed stock.
		$product_id = $this->factory->product->createSimple(
			[
				'manage_stock'   => true,
				'stock_quantity' => 50,
			]
		);

		$product = wc_get_product( $product_id );
		$this->assertEquals( 50, $product->get_stock_quantity(), 'Initial stock should be 50.' );

		$mutation = '
			mutation createOrder($input: CreateOrderInput!) {
				createOrder(input: $input) {
					order {
						databaseId
						status
					}
				}
			}
		';

		// Test with isPaid: true only (no explicit status).
		$variables = [
			'input' => [
				'clientMutationId' => 'stock-test-paid',
				'isPaid'           => true,
				'paymentMethod'    => 'cod',
				'lineItems'        => [
					[
						'productId' => $product_id,
						'quantity'  => 3,
					],
				],
			],
		];

		$response = $this->graphql(
			[
				'query'     => $mutation,
				'variables' => $variables,
			]
		);
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'createOrder.order.databaseId', self::NOT_NULL ) ]
		);

		$product = wc_get_product( $product_id );
		$this->assertEquals(
			47,
			$product->get_stock_quantity(),
			'Stock should be reduced from 50 to 47 after ordering 3 units with isPaid: true.'
		);

		// Test with bacs payment method — should result in PROCESSING status.
		$variables = [
			'input' => [
				'clientMutationId' => 'stock-test-bacs',
				'isPaid'           => true,
				'paymentMethod'    => 'bacs',
				'lineItems'        => [
					[
						'productId' => $product_id,
						'quantity'  => 2,
					],
				],
			],
		];

		$response = $this->graphql(
			[
				'query'     => $mutation,
				'variables' => $variables,
			]
		);
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'createOrder.order.databaseId', self::NOT_NULL ) ]
		);

		$product = wc_get_product( $product_id );
		$this->assertEquals(
			45,
			$product->get_stock_quantity(),
			'Stock should be reduced from 47 to 45 after ordering 2 units with BACS payment.'
		);

		// Test with isPaid: true and explicit status: COMPLETED.
		// Setting status manually alongside isPaid interferes with WooCommerce's
		// stock management flow. Users should rely on isPaid alone and let
		// WooCommerce determine the correct status.
		$variables = [
			'input' => [
				'clientMutationId' => 'stock-test-completed',
				'isPaid'           => true,
				'paymentMethod'    => 'bacs',
				'status'           => 'COMPLETED',
				'lineItems'        => [
					[
						'productId' => $product_id,
						'quantity'  => 5,
					],
				],
			],
		];

		$response = $this->graphql(
			[
				'query'     => $mutation,
				'variables' => $variables,
			]
		);
		$expected = [
			$this->expectedField( 'createOrder.order.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createOrder.order.status', 'COMPLETED' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		// Stock should NOT change when status is explicitly set alongside isPaid,
		// because the manual status override bypasses WooCommerce's stock reduction hooks.
		$product = wc_get_product( $product_id );
		$this->assertEquals(
			45,
			$product->get_stock_quantity(),
			'Stock should remain at 45 when status is explicitly set alongside isPaid.'
		);
	}

	/**
	 * Test that createOrder auto-fills line item name, subtotal, and total
	 * when only productId is provided.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/946
	 */
	public function testCreateOrderWithProductIdOnlyLineItems() {
		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => '25',
				'name'          => 'Test Widget',
			]
		);

		$this->loginAsShopManager();

		$query = '
			mutation ($input: CreateOrderInput!) {
				createOrder(input: $input) {
					order {
						lineItems {
							nodes {
								productId
								quantity
								total
								subtotal
								product {
									node {
										... on SimpleProduct {
											name
										}
									}
								}
							}
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'create-order-minimal',
				'paymentMethod'    => 'bacs',
				'lineItems'        => [
					[ 'productId' => $product_id ],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'createOrder.order.lineItems.nodes.0.productId', $product_id ),
			$this->expectedField( 'createOrder.order.lineItems.nodes.0.quantity', 1 ),
			$this->expectedField( 'createOrder.order.lineItems.nodes.0.total', '$25.00' ),
			$this->expectedField( 'createOrder.order.lineItems.nodes.0.subtotal', '$25.00' ),
			$this->expectedField( 'createOrder.order.lineItems.nodes.0.product.node.name', 'Test Widget' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
