<?php


class CheckoutMutationTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		// Force WP_Filesystem to use the direct method so WooCommerce's
		// FileV2 log handler doesn't attempt FTP operations which fail
		// in the test environment (null FTP connection on PHP 8.1+).
		add_filter( 'filesystem_method', function () {
			return 'direct';
		} );

		$this->loginAs( 0 );

		// Turn on tax calculations and store shipping countries. Important!
		update_option( 'woocommerce_ship_to_countries', 'all' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

		// Turn on guest checkout.
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );

		// Enable payment gateways.
		$gateways     = \WC()->payment_gateways->payment_gateways();
		$bacs_gateway = $gateways['bacs'];
		$bacs_gateway->settings['enabled'] = 'yes';
		update_option( $bacs_gateway->get_option_key(), $bacs_gateway->settings );
		$stripe_settings                         = WC_Stripe_Helper::get_stripe_settings();
		$stripe_settings['enabled']              = 'yes';
		$stripe_settings['testmode']             = 'yes';
		$stripe_settings['test_publishable_key'] = defined( 'STRIPE_API_PUBLISHABLE_KEY' )
			? STRIPE_API_PUBLISHABLE_KEY
			: getenv( 'STRIPE_API_PUBLISHABLE_KEY' );
		$stripe_settings['test_secret_key']      = defined( 'STRIPE_API_SECRET_KEY' )
			? STRIPE_API_SECRET_KEY
			: getenv( 'STRIPE_API_SECRET_KEY' );
		WC_Stripe_Helper::update_main_stripe_settings( $stripe_settings );
		$_SERVER['HTTPS'] = false;
		add_filter( 'wc_stripe_is_upe_checkout_enabled', '__return_false' );
		add_filter(
			'woocommerce_available_payment_gateways',
			function( $available_gateways ) {
				$stripe_gateway = new WC_Gateway_Stripe();
				$available_gateways[ $stripe_gateway->id ] = $stripe_gateway; 
				return $available_gateways;
			}
		);
		\WC()->payment_gateways->init();

		// Additional cart fees.
		add_action(
			'woocommerce_cart_calculate_fees',
			static function () {
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
			$this->expectedField( 'checkout.order.id', static::NOT_NULL ),
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
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'discount', static::NOT_NULL ),
					$this->expectedField( 'discountTax', static::NOT_NULL ),
					$this->expectedField( 'coupon', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.feeLines.nodes',
				[
					$this->expectedField( 'name', 'Surcharge' ),
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'amount', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Flat rate' ),
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', static::NOT_NULL ),
					$this->expectedField( 'taxTotal', static::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', static::NOT_NULL ),
					$this->expectedField( 'isCompound', static::NOT_NULL ),
					$this->expectedField( 'taxRate', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[2] ),
					$this->expectedField( 'variationId', static::NOT_NULL ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
					$this->expectedField( 'variation.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedField(
				'checkout.customer.id',
				$this->toRelayId( 'user', $this->customer )
			),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', static::NOT_NULL ),
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
			$this->expectedField( 'checkout.order.id', static::NOT_NULL ),
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
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'amount', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Flat rate' ),
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', static::NOT_NULL ),
					$this->expectedField( 'taxTotal', static::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', static::NOT_NULL ),
					$this->expectedField( 'isCompound', static::NOT_NULL ),
					$this->expectedField( 'taxRate', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[2] ),
					$this->expectedField( 'variationId', static::NOT_NULL ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
					$this->expectedField( 'variation.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.customer.id', static::NOT_NULL ),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', static::NOT_NULL ),
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
			$this->expectedField( 'checkout.order.id', static::NOT_NULL ),
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
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'amount', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Flat rate' ),
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', static::NOT_NULL ),
					$this->expectedField( 'taxTotal', static::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', static::NOT_NULL ),
					$this->expectedField( 'isCompound', static::NOT_NULL ),
					$this->expectedField( 'taxRate', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[2] ),
					$this->expectedField( 'variationId', static::NOT_NULL ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
					$this->expectedField( 'variation.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.customer.id', 'guest' ),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', static::NOT_NULL ),
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
			$this->expectedField( 'checkout.order.id', static::NOT_NULL ),
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
					$this->expectedField( 'databaseId', static::NOT_NULL ),
					$this->expectedField( 'orderId', static::NOT_NULL ),
					$this->expectedField( 'amount', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.taxLines.nodes',
				[
					$this->expectedField( 'label', 'VAT' ),
					$this->expectedField( 'rateCode', static::NOT_NULL ),
					$this->expectedField( 'taxTotal', static::NOT_NULL ),
					$this->expectedField( 'shippingTaxTotal', static::NOT_NULL ),
					$this->expectedField( 'isCompound', static::NOT_NULL ),
					$this->expectedField( 'taxRate', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[0] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedField( 'checkout.customer.id', 'guest' ),
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.redirect', static::NOT_NULL ),
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
			$this->expectedField( 'checkout.order.id', static::NOT_NULL ),
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
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
				]
			),
			$this->expectedNode(
				'checkout.order.lineItems.nodes',
				[
					$this->expectedField( 'productId', $product_ids[1] ),
					$this->expectedField( 'quantity', static::NOT_NULL ),
					$this->expectedField( 'taxClass', static::NOT_NULL ),
					$this->expectedField( 'subtotal', static::NOT_NULL ),
					$this->expectedField( 'subtotalTax', static::NOT_NULL ),
					$this->expectedField( 'total', static::NOT_NULL ),
					$this->expectedField( 'totalTax', static::NOT_NULL ),
					$this->expectedField( 'taxStatus', static::NOT_NULL ),
					$this->expectedField( 'product.node.id', static::NOT_NULL ),
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
		$expected = [ $this->expectedField( 'checkout', static::IS_NULL ) ];

		$this->assertQueryError( $response, $expected );
	}
}
