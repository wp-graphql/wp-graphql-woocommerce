<?php

use WPGraphQL\Type\WPEnumType;

class CheckoutMutationTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		$this->loginAs( 0 );

		// Turn on tax calculations and store shipping countries. Important!
		update_option( 'woocommerce_ship_to_countries', 'all' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

		// Turn on guest checkout.
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );

		// Enable payment gateways.
		update_option(
			'woocommerce_bacs_settings',
			[
				'enabled'      => 'yes',
				'title'        => 'Direct bank transfer',
				'description'  => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
				'instructions' => 'Instructions that will be added to the thank you page and emails.',
				'account'      => '',
			]
		);

		update_option(
			'woocommerce_stripe_settings',
			[
				'enabled'                       => 'yes',
				'title'                         => 'Credit Card (Stripe)',
				'description'                   => 'Pay with your credit card via Stripe',
				'webhook'                       => '',
				'testmode'                      => 'yes',
				'test_publishable_key'          => defined( 'STRIPE_API_PUBLISHABLE_KEY' )
					? STRIPE_API_PUBLISHABLE_KEY
					: getenv( 'STRIPE_API_PUBLISHABLE_KEY' ),
				'test_secret_key'               => defined( 'STRIPE_API_SECRET_KEY' )
					? STRIPE_API_SECRET_KEY
					: getenv( 'STRIPE_API_SECRET_KEY' ),
				'test_webhook_secret'           => '',
				'publishable_key'               => '',
				'secret_key'                    => '',
				'webhook_secret'                => '',
				'inline_cc_form'                => 'no',
				'statement_descriptor'          => '',
				'capture'                       => 'yes',
				'payment_request'               => 'yes',
				'payment_request_button_type'   => 'buy',
				'payment_request_button_theme'  => 'dark',
				'payment_request_button_height' => '44',
				'saved_cards'                   => 'yes',
				'logging'                       => 'no',
			]
		);

		// Additional cart fees.
		add_action(
			'woocommerce_cart_calculate_fees',
			function() {
				$percentage = 0.01;
				$surcharge  = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $percentage;
				WC()->cart->add_fee( 'Surcharge', $surcharge, true, '' );
			}
		);

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
	}

	private function getCheckoutMutation() {
		return '
			mutation checkout( $input: CheckoutInput! ) {
				checkout( input: $input ) {
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
										... on SimpleProduct {
											id
										}
										... on VariableProduct {
											id
										}
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
					customer {
						id
					}
					result
					redirect
				}
			}
		';
	}

	private function getCartQuery() {
		return '
			query {
				cart {
					contents {
						nodes {
							key
						}
					}
					total
				}
			}
		';
	}

	private function getCheckoutInput( $overwrite = [] ) {
		return array_merge(
			[
				'paymentMethod'  => 'bacs',
				'shippingMethod' => [ 'flat rate' ],
				'customerNote'   => 'Test customer note',
				'billing'        => [
					'firstName' => 'May',
					'lastName'  => 'Parker',
					'address1'  => '20 Ingram St',
					'city'      => 'New York City',
					'state'     => 'NY',
					'postcode'  => '12345',
					'country'   => 'US',
					'email'     => 'superfreak500@gmail.com',
					'phone'     => '555-555-1234',
					'overwrite' => true,
				],
				'shipping'       => [
					'firstName' => 'May',
					'lastName'  => 'Parker',
					'address1'  => '20 Ingram St',
					'city'      => 'New York City',
					'state'     => 'NY',
					'postcode'  => '12345',
					'country'   => 'US',
				],
				'metaData'       => [
					[
						'key'   => 'test_key',
						'value' => 'test value',
					],
				],
			],
			$overwrite
		);
	}

	// tests
	public function testCheckoutMutation() {
		$this->loginAsCustomer();
		WC()->customer->set_billing_company( 'Harris Teeter' );
		WC()->customer->save();

		$variable    = $this->factory->product_variation->createSome();
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create(
				[ 'product_ids' => $product_ids ]
			)
		);

		WC()->cart->add_to_cart( $product_ids[0], 3 );
		WC()->cart->add_to_cart( $product_ids[1], 6 );
		WC()->cart->add_to_cart(
			$product_ids[2],
			2,
			$variable['variations'][0],
			[ 'attribute_pa_color' => 'red' ]
		);
		WC()->cart->apply_coupon( $coupon->get_code() );

		$variables = [ 'input' => $this->getCheckoutInput() ];
		$query     = $this->getCheckoutMutation();

		/**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'checkout.order.id', self::NOT_NULL ),
			$this->expectedField( 'checkout.order.status', 'ON_HOLD' ),
			$this->expectedNode(
				'checkout.order.metaData',
				[
					$this->expectedField( 'key', 'test_key' ),
					$this->expectedField( 'value', 'test value' ),
				]
			),
			$this->expectedNode(
				'checkout.order.couponLines.nodes',
				[
					$this->expectedField( 'code', $coupon->get_code() ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'discount', self::NOT_NULL ),
					$this->expectedField( 'discountTax', self::NOT_NULL ),
					$this->expectedField( 'coupon', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.feeLines.nodes',
				[
					$this->expectedField( 'name', 'Surcharge' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'amount', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Flat rate' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', self::NOT_NULL ),
					$this->expectedField( 'taxTotal', self::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', self::NOT_NULL ),
					$this->expectedField( 'isCompound', self::NOT_NULL ),
					$this->expectedField( 'taxRate', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[2] ),
					$this->expectedField( 'variationId', self::NOT_NULL ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
					$this->expectedField( 'variation.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedField(
				'checkout.customer.id',
				$this->toRelayId( 'customer', $this->customer )
			),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', self::NOT_NULL ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm cart empty after successful checkout.
		$query    = $this->getCartQuery();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'cart.contents.nodes', [] ),
			$this->expectedField( 'cart.total', '$0.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCheckoutMutationWithNewAccount() {
		$variable    = $this->factory->product_variation->createSome();
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create(
				[ 'product_ids' => $product_ids ]
			)
		);

		WC()->cart->add_to_cart( $product_ids[0], 3 );
		WC()->cart->add_to_cart( $product_ids[1], 6 );
		WC()->cart->add_to_cart(
			$product_ids[2],
			2,
			$variable['variations'][0],
			[ 'attribute_pa_color' => 'red' ]
		);
		WC()->cart->apply_coupon( $coupon->get_code() );

		$input     = [
			'account' => [
				'username' => 'test_user_1',
				'password' => 'test_pass',
			],
		];
		$variables = [ 'input' => $this->getCheckoutInput( $input ) ];
		$query     = $this->getCheckoutMutation();

		/**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'checkout.order.id', self::NOT_NULL ),
			$this->expectedField( 'checkout.order.status', 'ON_HOLD' ),
			$this->expectedNode(
				'checkout.order.metaData',
				[
					$this->expectedField( 'key', 'test_key' ),
					$this->expectedField( 'value', 'test value' ),
				]
			),
			$this->expectedNode(
				'checkout.order.feeLines.nodes',
				[
					$this->expectedField( 'name', 'Surcharge' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'amount', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Flat rate' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', self::NOT_NULL ),
					$this->expectedField( 'taxTotal', self::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', self::NOT_NULL ),
					$this->expectedField( 'isCompound', self::NOT_NULL ),
					$this->expectedField( 'taxRate', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[2] ),
					$this->expectedField( 'variationId', self::NOT_NULL ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
					$this->expectedField( 'variation.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.customer.id', self::NOT_NULL ),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', self::NOT_NULL ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm cart empty after successful checkout.
		$query    = $this->getCartQuery();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'cart.contents.nodes', [] ),
			$this->expectedField( 'cart.total', '$0.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCheckoutMutationWithNoAccount() {
		WC()->customer->set_billing_email( 'superfreak500@gmail.com' );
		WC()->customer->save();

		$variable    = $this->factory->product_variation->createSome();
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
			$variable['product'],
		];
		$coupon      = new WC_Coupon(
			$this->factory->coupon->create(
				[ 'product_ids' => $product_ids ]
			)
		);

		WC()->cart->add_to_cart( $product_ids[0], 3 );
		WC()->cart->add_to_cart( $product_ids[1], 6 );
		WC()->cart->add_to_cart(
			$product_ids[2],
			2,
			$variable['variations'][0],
			[ 'attribute_pa_color' => 'red' ]
		);
		WC()->cart->apply_coupon( $coupon->get_code() );

		$variables = [ 'input' => $this->getCheckoutInput() ];
		$query     = $this->getCheckoutMutation();

		/**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'checkout.order.id', self::NOT_NULL ),
			$this->expectedField( 'checkout.order.status', 'ON_HOLD' ),
			$this->expectedNode(
				'checkout.order.metaData',
				[
					$this->expectedField( 'key', 'test_key' ),
					$this->expectedField( 'value', 'test value' ),
				]
			),
			$this->expectedNode(
				'checkout.order.feeLines.nodes',
				[
					$this->expectedField( 'name', 'Surcharge' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'amount', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Flat rate' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', self::NOT_NULL ),
					$this->expectedField( 'taxTotal', self::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', self::NOT_NULL ),
					$this->expectedField( 'isCompound', self::NOT_NULL ),
					$this->expectedField( 'taxRate', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[2] ),
					$this->expectedField( 'variationId', self::NOT_NULL ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
					$this->expectedField( 'variation.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.customer.id', 'guest' ),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', self::NOT_NULL ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm cart empty after successful checkout.
		$query    = $this->getCartQuery();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'cart.contents.nodes', [] ),
			$this->expectedField( 'cart.total', '$0.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCheckoutMutationWithPrepaidOrder() {
		WC()->customer->set_billing_email( 'superfreak500@gmail.com' );
		WC()->customer->save();

		$product_ids = [
			$this->factory->product->createSimple(
				[
					'virtual'      => true,
					'downloadable' => true,
				]
			),
			$this->factory->product->createSimple(
				[
					'virtual'      => true,
					'downloadable' => true,
				]
			),
		];

		$coupon = new WC_Coupon(
			$this->factory->coupon->create( [ 'product_ids' => $product_ids ] )
		);

		WC()->cart->add_to_cart( $product_ids[0], 3 );
		WC()->cart->add_to_cart( $product_ids[1], 6 );
		WC()->cart->apply_coupon( $coupon->get_code() );

		$input = [
			'isPaid'        => true,
			'transactionId' => 'transaction_id',
		];

		$variables = [ 'input' => $this->getCheckoutInput( $input ) ];
		$query     = $this->getCheckoutMutation();

		/**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'checkout.order.id', self::NOT_NULL ),
			$this->expectedField( 'checkout.order.status', 'COMPLETED' ),
			$this->expectedNode(
				'checkout.order.metaData',
				[
					$this->expectedField( 'key', 'test_key' ),
					$this->expectedField( 'value', 'test value' ),
				]
			),
			$this->expectedNode(
				'checkout.order.feeLines.nodes',
				[
					$this->expectedField( 'name', 'Surcharge' ),
					$this->expectedField( 'databaseId', self::NOT_NULL ),
					$this->expectedField( 'orderId', self::NOT_NULL ),
					$this->expectedField( 'amount', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', self::NOT_NULL ),
					$this->expectedField( 'taxTotal', self::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', self::NOT_NULL ),
					$this->expectedField( 'isCompound', self::NOT_NULL ),
					$this->expectedField( 'taxRate', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.customer.id', 'guest' ),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', self::NOT_NULL ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm cart empty after successful checkout.
		$query    = $this->getCartQuery();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'cart.contents.nodes', [] ),
			$this->expectedField( 'cart.total', '$0.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Returns a new Stripe Customer object
	 *
	 * @param string $email  Customer email
	 *
	 * @return array
	 */
	private function create_stripe_customer( $email ) {
		$customer = \Stripe\Customer::create( [ 'email' => $email ] );

		// use --debug flag to view.
		codecept_debug( $customer );
		return $customer;
	}

	/**
	 * Creates a new Stripe Source object, attaches it to the provided customer,
	 * and returns the Source object
	 *
	 * @param array $customer  Customer object
	 *
	 * @return array
	 */
	private function create_stripe_source( $customer ) {
		$source = \Stripe\Customer::createSource(
			$customer['id'],
			[ 'source' => 'tok_visa' ]
		);

		// use --debug flag to view.
		codecept_debug( $source );

		return $source;
	}

	/**
	 * Creates a new Payment Intent object, assigns an amount and customer,
	 * and returns the Payment Intent object
	 *
	 * This payment intent is meant to be processed by WooCommerce upon validation
	 * so do not `confirm=true` must not be passed as a parameter.
	 *
	 * @param array $customer  Customer object
	 *
	 * @return array
	 */
	private function create_stripe_payment_intent( $amount, $customer ) {
		$payment_intent = \Stripe\PaymentIntent::create(
			[
				'amount'               => $amount,
				'currency'             => 'gbp',
				'payment_method_types' => [ 'card' ],
				'customer'             => $customer['id'],
				'payment_method'       => $customer['invoice_settings']['default_payment_method'],
			]
		);

		// use --debug flag to view.
		codecept_debug( $payment_intent );

		return $payment_intent;
	}

	public function testCheckoutMutationWithStripe() {
		WC()->customer->set_billing_email( 'superfreak500@gmail.com' );
		WC()->customer->save();

		// Add items to the cart.
		$product_ids = [
			$this->factory->product->createSimple(),
			$this->factory->product->createSimple(),
		];
		WC()->cart->add_to_cart( $product_ids[0], 1 );
		WC()->cart->add_to_cart( $product_ids[1], 1 );

		$amount = (int) floatval( WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax() ) * 100;

		try {
			$stripe_customer = $this->create_stripe_customer( 'superfreak500@gmail.com' );
			$stripe_source   = $this->create_stripe_source( $stripe_customer );
			$payment_intent  = $this->create_stripe_payment_intent( $amount, $stripe_customer );
		} catch ( \Stripe\Exception\AuthenticationException $e ) {
			$this->markTestSkipped( $e->getMessage() );
		}

		$input = [
			'paymentMethod' => 'stripe',
			'metaData'      => [
				[
					'key'   => '_stripe_source_id',
					'value' => $stripe_source['id'],
				],
				[
					'key'   => '_stripe_customer_id',
					'value' => $stripe_customer['id'],
				],
				[
					'key'   => '_stripe_intent_id',
					'value' => $payment_intent['id'],
				],
			],
		];

		$variables = [ 'input' => $this->getCheckoutInput( $input ) ];
		// Remove "metaData" value field and "redirect" link from the mutation output.
		$query = '
            mutation checkout( $input: CheckoutInput! ) {
                checkout( input: $input ) {
                    clientMutationId
                    order {
						id
						status
						total
                        databaseId
                        metaData { key }
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
                    result
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'checkout.order.id', self::NOT_NULL ),
			$this->expectedField( 'checkout.order.status', 'PROCESSING' ),
			$this->expectedNode(
				'checkout.order.metaData',
				[ $this->expectedField( 'key', '_stripe_source_id' ) ]
			),
			$this->expectedNode(
				'checkout.order.metaData',
				[ $this->expectedField( 'key', '_stripe_customer_id' ) ]
			),
			$this->expectedNode(
				'checkout.order.metaData',
				[ $this->expectedField( 'key', '_stripe_intent_id' ) ]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', self::NOT_NULL ),
					$this->expectedField( 'taxClass', self::NOT_NULL ),
					$this->expectedField( 'subtotal', self::NOT_NULL ),
					$this->expectedField( 'subtotalTax', self::NOT_NULL ),
					$this->expectedField( 'total', self::NOT_NULL ),
					$this->expectedField( 'totalTax', self::NOT_NULL ),
					$this->expectedField( 'taxStatus', self::NOT_NULL ),
					$this->expectedField( 'product.node.id', self::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.result', 'success' ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm cart empty after successful checkout.
		$query    = $this->getCartQuery();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'cart.contents.nodes', [] ),
			$this->expectedField( 'cart.total', '$0.00' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCheckoutMutationCartItemValidation() {
		add_filter( 'woocommerce_hold_stock_for_checkout', '__return_false' );

		$product_id = $this->factory->product->createSimple(
			[
				'manage_stock'   => true,
				'stock_quantity' => 3,
			]
		);

		$key = WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->set_quantity( $key, 5 );

		$input     = [
			'account' => [
				'username' => 'test_user_1',
				'password' => 'test_pass',
			],
		];
		$variables = [ 'input' => $this->getCheckoutInput( $input ) ];
		$query     = $this->getCheckoutMutation();

				/**
		 * Assertion One
		 *
		 * Ensure that checkout failed when stock is too low.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'checkout', self::IS_NULL ) ];

		$this->assertQueryError( $response, $expected );
	}
}
