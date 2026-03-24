<?php

use WPGraphQL\Type\WPEnumType;

class OrderMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		// Create users.
		$this->shop_manager = $this->factory->user->create( [ 'role' => 'shop_manager' ] );
		$this->customer     = $this->factory->user->create( [ 'role' => 'customer' ] );

		// Get helper instances
		$this->order     = $this->getModule( '\Helper\Wpunit' )->order();
		$this->coupon    = $this->getModule( '\Helper\Wpunit' )->coupon();
		$this->product   = $this->getModule( '\Helper\Wpunit' )->product();
		$this->variation = $this->getModule( '\Helper\Wpunit' )->product_variation();
		$this->cart      = $this->getModule( '\Helper\Wpunit' )->cart();
		$this->tax       = $this->getModule( '\Helper\Wpunit' )->tax_rate();

		// Turn on tax calculations. Important!
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

		// Enable stock management.
		update_option( 'woocommerce_manage_stock', 'yes' );

		// Create a tax rate.
		$this->tax->create(
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
		$this->order_id = $this->order->create();
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
                                download {
                                    downloadId
                                }
                            }
                        }
                        needsPayment
                        needsProcessing
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
										... on SimpleProduct {
											id
										}
										... on VariableProduct {
											id
										}
									}
                                }
                                variation {
                                    node { id }
                                }
                            }
                        }
                    }
                }
            }
        ";

		return graphql(
			[
				'query'          => $mutation,
				'operation_name' => $operation_name,
				'variables'      => [ 'input' => $input ],
			]
		);
	}

	// tests
	public function testCreateOrderMutation() {
		$variable    = $this->variation->create( $this->product->create_variable() );
		$product_ids = [
			$this->product->create_simple(),
			$this->product->create_simple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->coupon->create( [ 'product_ids' => $product_ids ] )
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
		wp_set_current_user( $this->customer );
		$actual = $this->orderMutation( $input );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'errors', $actual );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		wp_set_current_user( $this->shop_manager );
		$actual = $this->orderMutation( $input );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'createOrder', $actual['data'] );
		$this->assertArrayHasKey( 'order', $actual['data']['createOrder'] );
		$this->assertArrayHasKey( 'id', $actual['data']['createOrder']['order'] );
		$order = \WC_Order_Factory::get_order( $actual['data']['createOrder']['order']['databaseId'] );

		$expected = [
			'data' => [
				'createOrder' => [
					'clientMutationId' => 'someId',
					'order'            => array_merge(
						$this->order->print_query( $order->get_id() ),
						[
							'couponLines'   => [
								'nodes' => array_reverse(
									array_map(
										function ( $item ) {
											return [
												'databaseId' => $item->get_id(),
												'orderId'  => $item->get_order_id(),
												'code'     => $item->get_code(),
												'discount' => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
												'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
												'coupon'   => [
													'id' => $this->coupon->to_relay_id( \wc_get_coupon_id_by_code( $item->get_code() ) ),
												],
											];
										},
										$order->get_items( 'coupon' )
									)
								),
							],
							'feeLines'      => [
								'nodes' => array_reverse(
									array_map(
										static function ( $item ) {
											return [
												'databaseId' => $item->get_id(),
												'orderId'  => $item->get_order_id(),
												'amount'   => $item->get_amount(),
												'name'     => $item->get_name(),
												'taxStatus' => strtoupper( $item->get_tax_status() ),
												'total'    => $item->get_total(),
												'totalTax' => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
												'taxClass' => ! empty( $item->get_tax_class() )
													? WPEnumType::get_safe_name( $item->get_tax_class() )
													: 'STANDARD',
											];
										},
										$order->get_items( 'fee' )
									)
								),
							],
							'shippingLines' => [
								'nodes' => array_reverse(
									array_map(
										static function ( $item ) {
											return [
												'databaseId' => $item->get_id(),
												'orderId'  => $item->get_order_id(),
												'methodTitle' => $item->get_method_title(),
												'total'    => $item->get_total(),
												'totalTax' => ! empty( $item->get_total_tax() )
													? $item->get_total_tax()
													: null,
												'taxClass' => ! empty( $item->get_tax_class() )
													? $item->get_tax_class() === 'inherit'
														? WPEnumType::get_safe_name( 'inherit cart' )
														: WPEnumType::get_safe_name( $item->get_tax_class() )
													: 'STANDARD',
											];
										},
										$order->get_items( 'shipping' )
									)
								),
							],
							'taxLines'      => [
								'nodes' => array_reverse(
									array_map(
										static function ( $item ) {
											return [
												'rateCode' => $item->get_rate_code(),
												'label'    => $item->get_label(),
												'taxTotal' => $item->get_tax_total(),
												'shippingTaxTotal' => $item->get_shipping_tax_total(),
												'isCompound' => $item->is_compound(),
												'taxRate'  => [ 'databaseId' => $item->get_rate_id() ],
											];
										},
										$order->get_items( 'tax' )
									)
								),
							],
							'lineItems'     => [
								'nodes' => array_values(
									array_map(
										function ( $item ) {
											return [
												'productId' => $item->get_product_id(),
												'variationId' => ! empty( $item->get_variation_id() )
													? $item->get_variation_id()
													: null,
												'quantity' => $item->get_quantity(),
												'taxClass' => ! empty( $item->get_tax_class() )
													? strtoupper( $item->get_tax_class() )
													: 'STANDARD',
												'subtotal' => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
												'subtotalTax' => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
												'total'    => ! empty( $item->get_total() ) ? $item->get_total() : null,
												'totalTax' => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
												'taxStatus' => strtoupper( $item->get_tax_status() ),
												'product'  => [ 'node' => [ 'id' => $this->product->to_relay_id( $item->get_product_id() ) ] ],
												'variation' => ! empty( $item->get_variation_id() )
													? [
														'node' => [
															'id' => $this->variation->to_relay_id( $item->get_variation_id() ),
														],
													]
													: null,
											];
										},
										$order->get_items()
									)
								),
							],
						]
					),
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testUpdateOrderMutation() {
		// Create products and coupons to be used in order creation.
		$variable    = $this->variation->create( $this->product->create_variable() );
		$product_ids = [
			$this->product->create_simple(),
			$this->product->create_simple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->coupon->create( [ 'product_ids' => $product_ids ] )
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
		wp_set_current_user( $this->shop_manager );
		$initial_response = $this->orderMutation( $initial_input );

		// use --debug flag to view.
		codecept_debug( $initial_response );

		// Retrieve order and items
		$order          = \WC_Order_Factory::get_order( $initial_response['data']['createOrder']['order']['databaseId'] );
		$line_items     = $order->get_items();
		$shipping_lines = $order->get_items( 'shipping' );
		$fee_lines      = $order->get_items( 'fee' );

		// Create update order input.
		$updated_input = [
			'id'               => $this->order->to_relay_id( $order->get_id() ),
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
		$actual = $this->orderMutation(
			$updated_input,
			'updateOrder',
			'UpdateOrderInput'
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'errors', $actual );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		wp_set_current_user( $this->shop_manager );
		$actual = $this->orderMutation(
			$updated_input,
			'updateOrder',
			'UpdateOrderInput'
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		// Apply new changes to order instances.
		$order = \WC_Order_Factory::get_order( $order->get_id() );

		$expected = [
			'data' => [
				'updateOrder' => [
					'clientMutationId' => 'someId',
					'order'            => array_merge(
						$this->order->print_query( $order->get_id() ),
						[
							'couponLines'   => [
								'nodes' => array_reverse(
									array_map(
										function ( $item ) {
											return [
												'databaseId' => $item->get_id(),
												'orderId'  => $item->get_order_id(),
												'code'     => $item->get_code(),
												'discount' => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
												'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
												'coupon'   => [
													'id' => $this->coupon->to_relay_id( \wc_get_coupon_id_by_code( $item->get_code() ) ),
												],
											];
										},
										$order->get_items( 'coupon' )
									)
								),
							],
							'feeLines'      => [
								'nodes' => array_reverse(
									array_map(
										static function ( $item ) {
											return [
												'databaseId' => $item->get_id(),
												'orderId'  => $item->get_order_id(),
												'amount'   => ! empty( $item->get_amount() ) ? $item->get_amount() : null,
												'name'     => $item->get_name(),
												'taxStatus' => strtoupper( $item->get_tax_status() ),
												'total'    => $item->get_total(),
												'totalTax' => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
												'taxClass' => ! empty( $item->get_tax_class() )
													? WPEnumType::get_safe_name( $item->get_tax_class() )
													: 'STANDARD',
											];
										},
										$order->get_items( 'fee' )
									)
								),
							],
							'shippingLines' => [
								'nodes' => array_reverse(
									array_map(
										static function ( $item ) {
											return [
												'databaseId' => $item->get_id(),
												'orderId'  => $item->get_order_id(),
												'methodTitle' => $item->get_method_title(),
												'total'    => $item->get_total(),
												'totalTax' => ! empty( $item->get_total_tax() )
													? $item->get_total_tax()
													: null,
												'taxClass' => ! empty( $item->get_tax_class() )
													? $item->get_tax_class() === 'inherit'
														? WPEnumType::get_safe_name( 'inherit cart' )
														: WPEnumType::get_safe_name( $item->get_tax_class() )
													: 'STANDARD',
											];
										},
										$order->get_items( 'shipping' )
									)
								),
							],
							'taxLines'      => [
								'nodes' => array_reverse(
									array_map(
										static function ( $item ) {
											return [
												'rateCode' => $item->get_rate_code(),
												'label'    => $item->get_label(),
												'taxTotal' => $item->get_tax_total(),
												'shippingTaxTotal' => $item->get_shipping_tax_total(),
												'isCompound' => $item->is_compound(),
												'taxRate'  => [ 'databaseId' => $item->get_rate_id() ],
											];
										},
										$order->get_items( 'tax' )
									)
								),
							],
							'lineItems'     => [
								'nodes' => array_values(
									array_map(
										function ( $item ) {
											return [
												'productId' => $item->get_product_id(),
												'variationId' => ! empty( $item->get_variation_id() )
													? $item->get_variation_id()
													: null,
												'quantity' => $item->get_quantity(),
												'taxClass' => ! empty( $item->get_tax_class() )
													? strtoupper( $item->get_tax_class() )
													: 'STANDARD',
												'subtotal' => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
												'subtotalTax' => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
												'total'    => ! empty( $item->get_total() ) ? $item->get_total() : null,
												'totalTax' => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
												'taxStatus' => strtoupper( $item->get_tax_status() ),
												'product'  => [ 'node' => [ 'id' => $this->product->to_relay_id( $item->get_product_id() ) ] ],
												'variation' => ! empty( $item->get_variation_id() )
													? [
														'node' => [
															'id' => $this->variation->to_relay_id( $item->get_variation_id() ),
														],
													]
													: null,
											];
										},
										$order->get_items()
									)
								),
							],
						]
					),
				],
			],
		];

		$this->assertEquals( $expected, $actual );
		$this->assertNotEquals( $initial_response, $actual );
	}

	public function testDeleteOrderMutation() {
		// Create products and coupons to be used in order creation.
		$variable    = $this->variation->create( $this->product->create_variable() );
		$product_ids = [
			$this->product->create_simple(),
			$this->product->create_simple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->coupon->create( [ 'product_ids' => $product_ids ] )
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
		wp_set_current_user( $this->shop_manager );
		$initial_response = $this->orderMutation( $initial_input );

		// use --debug flag to view.
		codecept_debug( $initial_response );

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
			'id'               => $this->order->to_relay_id( $order->get_id() ),
			'forceDelete'      => true,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot delete order an order.
		 */
		wp_set_current_user( $this->factory->user->create( [ 'role' => 'customer' ] ) );
		$actual = $this->orderMutation(
			$deleted_input,
			'deleteOrder',
			'DeleteOrderInput'
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'errors', $actual );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		wp_set_current_user( $this->shop_manager );
		$actual = $this->orderMutation(
			$deleted_input,
			'deleteOrder',
			'DeleteOrderInput'
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'deleteOrder', $actual['data'] );
		$this->assertEquals( $initial_response['data']['createOrder'], $actual['data']['deleteOrder'] );
		$this->assertFalse( \WC_Order_Factory::get_order( $order->get_id() ) );
	}

	public function testDeleteOrderItemsMutation() {
		// Create products and coupons to be used in order creation.
		$variable    = $this->variation->create( $this->product->create_variable() );
		$product_ids = [
			$this->product->create_simple(),
			$this->product->create_simple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->coupon->create( [ 'product_ids' => $product_ids ] )
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
		wp_set_current_user( $this->shop_manager );
		$initial_response = $this->orderMutation( $initial_input );

		// use --debug flag to view.
		codecept_debug( $initial_response );

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
		$actual = $this->orderMutation(
			$deleted_items_input,
			'deleteOrderItems',
			'DeleteOrderItemsInput'
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'errors', $actual );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		wp_set_current_user( $this->shop_manager );
		$actual = $this->orderMutation(
			$deleted_items_input,
			'deleteOrderItems',
			'DeleteOrderItemsInput'
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'deleteOrderItems', $actual['data'] );
		$this->assertEquals( $initial_response['data']['createOrder'], $actual['data']['deleteOrderItems'] );
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
		$actual = $this->orderNoteMutation( $input );

		$this->assertQueryError( $actual );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$actual = $this->orderNoteMutation( $input );

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

		$this->assertQuerySuccessful( $actual, $expected );

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
		// First create a note to delete
		$this->loginAsShopManager();
		$create_input = [
			'clientMutationId' => 'createId',
			'orderId'          => $this->order_id,
			'note'             => 'Note to be deleted',
			'isCustomerNote'   => false,
		];

		$create_result = $this->orderNoteMutation( $create_input );
		// Get note ID from the proper expected field result
		$note_id = $this->lodashGet( $create_result, 'data.createOrderNote.orderNote.databaseId' );

		$delete_input = [
			'clientMutationId' => 'deleteId',
			'id'               => $note_id,
			'orderId'          => $this->order_id,
			'force'            => true,
		];

		/**
		 * Assertion One
		 *
		 * User without necessary capabilities cannot delete an order note.
		 */
		$this->loginAsCustomer();
		$actual = $this->orderNoteMutation( $delete_input, 'deleteOrderNote', 'DeleteOrderNoteInput' );

		$this->assertQueryError( $actual );

		/**
		 * Assertion Two
		 *
		 * Test mutation and input.
		 */
		$this->loginAsShopManager();
		$actual = $this->orderNoteMutation( $delete_input, 'deleteOrderNote', 'DeleteOrderNoteInput' );

		$expected = [
			$this->expectedField( 'deleteOrderNote.clientMutationId', 'deleteId' ),
			$this->expectedField( 'deleteOrderNote.orderNote.note', 'Note to be deleted' ),
			$this->expectedField( 'deleteOrderNote.order.databaseId', $this->order_id ),
		];

		$this->assertQuerySuccessful( $actual, $expected );

		// Verify the note was deleted by checking it doesn't exist
		$deleted_note = get_comment( $note_id );
		$this->assertNull( $deleted_note );
	}

	public function testCreateOrderNoteValidation() {
		$this->loginAsShopManager();

		// Test missing note content
		$invalid_input = [
			'clientMutationId' => 'invalidId',
			'orderId'          => $this->order_id,
			'note'             => '',
			'isCustomerNote'   => false,
		];

		$actual = $this->orderNoteMutation( $invalid_input );
		$this->assertQueryError( $actual );

		// Test invalid order ID
		$invalid_order_input = [
			'clientMutationId' => 'invalidOrderId',
			'orderId'          => 99999,
			'note'             => 'Valid note content',
			'isCustomerNote'   => false,
		];

		$actual = $this->orderNoteMutation( $invalid_order_input );
		$this->assertQueryError( $actual );
	}

	/**
	 * Test that updateOrder with only metaData does not create a duplicate order.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/591
	 */
	public function testUpdateOrderMetaDataDoesNotDuplicate() {
		wp_set_current_user( $this->shop_manager );

		// Create an order.
		$order_id = $this->order->create(
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
		wp_set_current_user( $this->shop_manager );

		// Create a product with managed stock.
		$product_id = $this->product->create_simple(
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
		$expected = [
			$this->expectedField( 'createOrder.order.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createOrder.order.status', 'COMPLETED' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$product = wc_get_product( $product_id );
		$this->assertEquals(
			47,
			$product->get_stock_quantity(),
			'Stock should be reduced from 50 to 47 after ordering 3 units with COD payment.'
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
		$expected = [
			$this->expectedField( 'createOrder.order.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createOrder.order.status', 'PROCESSING' ),
		];
		$this->assertQuerySuccessful( $response, $expected );

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
}
