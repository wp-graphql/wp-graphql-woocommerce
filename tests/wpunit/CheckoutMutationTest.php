<?php

use WPGraphQL\Type\WPEnumType;

class CheckoutMutationTest extends \Codeception\TestCase\WPTestCase {
    public function setUp() {
        // before
        parent::setUp();

        // Create users.
        $this->shop_manager    = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
        $this->simple_customer = $this->factory->user->create( array( 'role' => 'customer' ) );

        // Get helper instances
        $this->order      = $this->getModule('\Helper\Wpunit')->order();
        $this->coupon     = $this->getModule('\Helper\Wpunit')->coupon();
        $this->product    = $this->getModule('\Helper\Wpunit')->product();
        $this->variation  = $this->getModule('\Helper\Wpunit')->product_variation();
        $this->cart       = $this->getModule('\Helper\Wpunit')->cart();
        $this->tax        = $this->getModule('\Helper\Wpunit')->tax_rate();
        $this->customer   = $this->getModule('\Helper\Wpunit')->customer();

        // Turn on tax calculations and store shipping countries. Important!
        update_option( 'woocommerce_ship_to_countries', 'all' );
        update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );
        update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

        // Enable payment gateways.
        update_option(
            'woocommerce_bacs_settings',
            array(
                'enabled'      => 'yes',
                'title'        => 'Direct bank transfer',
                'description'  => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
                'instructions' => 'Instructions that will be added to the thank you page and emails.',
                'account'      => '',
            )
        );

        update_option(
            'woocommerce_stripe_settings',
            array(
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
            )
        );

        // Additional cart fees.
        add_action(
            'woocommerce_cart_calculate_fees',
            function() {
                $percentage = 0.01;
                $surcharge = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $percentage;
                WC()->cart->add_fee( 'Surcharge', $surcharge, true, '' );
            }
        );

        // Create a tax rate.
        $this->tax->create(
            array(
                'country'  => '',
                'state'    => '',
                'rate'     => 20.000,
                'name'     => 'VAT',
                'priority' => '1',
                'compound' => '0',
                'shipping' => '1',
                'class'    => ''
            )
        );
        // Create sample order to be used as a parent order.
        $this->order_id = $this->order->create();

        // Clear cart.
        WC()->cart->empty_cart( true );
        wp_logout();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    private function checkout( $input, $mutation = null ) {
        if ( ! $mutation ) {
            $mutation = '
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
                                        ... on SimpleProduct {
                                            id
                                        }
                                        ... on VariableProduct {
                                            id
                                        }
                                    }
                                    variation {
                                        id
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

        $actual = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'checkout',
                'variables'      => array( 'input' => $input  ),
            )
        );

        return $actual;
    }

    // tests
    public function testCheckoutMutation() {
        wp_set_current_user( $this->simple_customer );
        WC()->customer->set_billing_company( 'Harris Teeter' );

        $variable  = $this->variation->create( $this->product->create_variable() );
        $product_ids = array(
            $this->product->create_simple(),
            $this->product->create_simple(),
            $variable['product'],
        );
        $coupon     = new WC_Coupon(
            $this->coupon->create( array( 'product_ids' => $product_ids ) )
		);

        WC()->cart->add_to_cart( $product_ids[0], 3 );
        WC()->cart->add_to_cart( $product_ids[1], 6 );
        WC()->cart->add_to_cart(
			$product_ids[2],
			2,
			$variable['variations'][0],
			array( 'attribute_pa_color' => 'red' )
		);
        WC()->cart->apply_coupon( $coupon->get_code() );

        $input      = array(
            'clientMutationId'   => 'someId',
            'paymentMethod'      => 'bacs',
            'shippingMethod'     => array( 'flat rate' ),
            'customerNote'       => 'Test customer note',
			'billing'            => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
                'overwrite' => true
            ),
			'shipping'           => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
            ),
            'metaData'           => array(
                array(
                    'key'   => 'test_key',
                    'value' => 'test value',
                ),
            )
        );

        /**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
        $actual = $this->checkout( $input );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('checkout', $actual['data'] );
        $this->assertArrayHasKey('order', $actual['data']['checkout'] );
        $this->assertArrayHasKey('id', $actual['data']['checkout']['order'] );
        $order = \WC_Order_Factory::get_order( $actual['data']['checkout']['order']['databaseId'] );

        // Get Available payment gateways.
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $expected = array(
            'data' => array(
                'checkout' => array(
                    'clientMutationId' => 'someId',
                    'order'            => array_merge(
                        $this->order->print_query( $order->get_id() ),
                        array(
                            'metaData'      => array(
                                array(
                                    'key'   => 'is_vat_exempt',
                                    'value' => 'no',
                                ),
                                array(
                                    'key'   => 'test_key',
                                    'value' => 'test value',
                                ),
                            ),
                            'couponLines'   => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId'  => $item->get_id(),
                                                'orderId'     => $item->get_order_id(),
                                                'code'        => $item->get_code(),
                                                'discount'    => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
                                                'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
                                                'coupon'      => null,
                                            );
                                        },
                                        $order->get_items( 'coupon' )
                                    )
                                ),
                            ),
                            'feeLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId' => $item->get_id(),
                                                'orderId'    => $item->get_order_id(),
                                                'amount'     => $item->get_amount(),
                                                'name'       => $item->get_name(),
                                                'taxStatus'  => strtoupper( $item->get_tax_status() ),
                                                'total'      => $item->get_total(),
                                                'totalTax'   => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxClass'   => ! empty( $item->get_tax_class() )
                                                    ? WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD',
                                            );
                                        },
                                        $order->get_items( 'fee' )
                                    )
                                ),
                            ),
                            'shippingLines' => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {

                                            return array(
                                                'databaseId'  => $item->get_id(),
                                                'orderId'     => $item->get_order_id(),
                                                'methodTitle' => $item->get_method_title(),
                                                'total'       => $item->get_total(),
                                                'totalTax'    => !empty( $item->get_total_tax() )
                                                    ? $item->get_total_tax()
                                                    : null,
                                                'taxClass'    => ! empty( $item->get_tax_class() )
                                                    ? $item->get_tax_class() === 'inherit'
                                                        ? WPEnumType::get_safe_name( 'inherit cart' )
                                                        : WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD'
                                            );
                                        },
                                        $order->get_items( 'shipping' )
                                    )
                                ),
                            ),
                            'taxLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'rateCode'         => $item->get_rate_code(),
                                                'label'            => $item->get_label(),
                                                'taxTotal'         => $item->get_tax_total(),
                                                'shippingTaxTotal' => $item->get_shipping_tax_total(),
                                                'isCompound'       => $item->is_compound(),
                                                'taxRate'          => array( 'databaseId' => $item->get_rate_id() ),
                                            );
                                        },
                                        $order->get_items( 'tax' )
                                    )
                                ),
                            ),
                            'lineItems'     => array(
                                'nodes' => array_values(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'productId'     => $item->get_product_id(),
                                                'variationId'   => ! empty( $item->get_variation_id() )
                                                    ? $item->get_variation_id()
                                                    : null,
                                                'quantity'      => $item->get_quantity(),
                                                'taxClass'      => ! empty( $item->get_tax_class() )
                                                    ? strtoupper( $item->get_tax_class() )
                                                    : 'STANDARD',
                                                'subtotal'      => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
                                                'subtotalTax'   => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
                                                'total'         => ! empty( $item->get_total() ) ? $item->get_total() : null,
                                                'totalTax'      => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxStatus'     => strtoupper( $item->get_tax_status() ),
                                                'product'       => array( 'id' => $this->product->to_relay_id( $item->get_product_id() ) ),
                                                'variation'     => ! empty( $item->get_variation_id() )
                                                    ? array(
                                                        'id' => $this->variation->to_relay_id( $item->get_variation_id() )
                                                    )
                                                    : null,
                                            );
                                        },
                                        $order->get_items()
                                    )
                                ),
                            ),
                        )
                    ),
                    'customer'         => array(
                        'id' => $this->customer->to_relay_id( $order->get_customer_id() )
                    ),
                    'result'           => 'success',
                    'redirect'         => $available_gateways['bacs']->process_payment( $order->get_id() )['redirect'],
                ),
            )
        );

        $this->assertEquals( $expected, $actual );
    }

    public function testCheckoutMutationWithNewAccount() {
        $variable  = $this->variation->create( $this->product->create_variable() );
        $product_ids = array(
            $this->product->create_simple(),
            $this->product->create_simple(),
            $variable['product'],
        );
        $coupon     = new WC_Coupon(
            $this->coupon->create( array( 'product_ids' => $product_ids ) )
        );
        WC()->cart->add_to_cart( $product_ids[0], 3 );
        WC()->cart->add_to_cart( $product_ids[1], 6 );
        WC()->cart->add_to_cart(
			$product_ids[2],
			2,
			$variable['variations'][0],
			array( 'attribute_pa_color' => 'red' )
		);
        WC()->cart->apply_coupon( $coupon->get_code() );

        $input      = array(
            'clientMutationId'       => 'someId',
            'paymentMethod'          => 'bacs',
            'shippingMethod'         => array( 'flat rate' ),
			'billing'                => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'company'   => 'Harris Teeter',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
            ),
            'account'                => array(
                'username' => 'test_user_1',
                'password' => 'test_pass'
            )
        );

        /**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
        $actual = $this->checkout( $input );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('checkout', $actual['data'] );
        $this->assertArrayHasKey('order', $actual['data']['checkout'] );
        $this->assertArrayHasKey('id', $actual['data']['checkout']['order'] );
        $order = \WC_Order_Factory::get_order( $actual['data']['checkout']['order']['databaseId'] );

        // Get Available payment gateways.
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $expected = array(
            'data' => array(
                'checkout' => array(
                    'clientMutationId' => 'someId',
                    'order'            => array_merge(
                        $this->order->print_query( $order->get_id() ),
                        array(
                            'metaData'      => array(
                                array(
                                    'key'   => 'is_vat_exempt',
                                    'value' => 'no',
                                ),
                            ),
                            'couponLines'   => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId'  => $item->get_id(),
                                                'orderId'     => $item->get_order_id(),
                                                'code'        => $item->get_code(),
                                                'discount'    => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
                                                'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
                                                'coupon'      => null,
                                            );
                                        },
                                        $order->get_items( 'coupon' )
                                    )
                                ),
                            ),
                            'feeLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId' => $item->get_id(),
                                                'orderId'    => $item->get_order_id(),
                                                'amount'     => $item->get_amount(),
                                                'name'       => $item->get_name(),
                                                'taxStatus'  => strtoupper( $item->get_tax_status() ),
                                                'total'      => $item->get_total(),
                                                'totalTax'   => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxClass'   => ! empty( $item->get_tax_class() )
                                                    ? WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD',
                                            );
                                        },
                                        $order->get_items( 'fee' )
                                    )
                                ),
                            ),
                            'shippingLines' => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {

                                            return array(
                                                'databaseId'     => $item->get_id(),
                                                'orderId'        => $item->get_order_id(),
                                                'methodTitle'    => $item->get_method_title(),
                                                'total'          => $item->get_total(),
                                                'totalTax'       => !empty( $item->get_total_tax() )
                                                    ? $item->get_total_tax()
                                                    : null,
                                                'taxClass'       => ! empty( $item->get_tax_class() )
                                                    ? $item->get_tax_class() === 'inherit'
                                                        ? WPEnumType::get_safe_name( 'inherit cart' )
                                                        : WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD'
                                            );
                                        },
                                        $order->get_items( 'shipping' )
                                    )
                                ),
                            ),
                            'taxLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'rateCode'         => $item->get_rate_code(),
                                                'label'            => $item->get_label(),
                                                'taxTotal'         => $item->get_tax_total(),
                                                'shippingTaxTotal' => $item->get_shipping_tax_total(),
                                                'isCompound'       => $item->is_compound(),
                                                'taxRate'          => array( 'databaseId' => $item->get_rate_id() ),
                                            );
                                        },
                                        $order->get_items( 'tax' )
                                    )
                                ),
                            ),
                            'lineItems'     => array(
                                'nodes' => array_values(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'productId'     => $item->get_product_id(),
                                                'variationId'   => ! empty( $item->get_variation_id() )
                                                    ? $item->get_variation_id()
                                                    : null,
                                                'quantity'      => $item->get_quantity(),
                                                'taxClass'      => ! empty( $item->get_tax_class() )
                                                    ? strtoupper( $item->get_tax_class() )
                                                    : 'STANDARD',
                                                'subtotal'      => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
                                                'subtotalTax'   => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
                                                'total'         => ! empty( $item->get_total() ) ? $item->get_total() : null,
                                                'totalTax'      => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxStatus'     => strtoupper( $item->get_tax_status() ),
                                                'product'       => array( 'id' => $this->product->to_relay_id( $item->get_product_id() ) ),
                                                'variation'     => ! empty( $item->get_variation_id() )
                                                    ? array(
                                                        'id' => $this->variation->to_relay_id( $item->get_variation_id() )
                                                    )
                                                    : null,
                                            );
                                        },
                                        $order->get_items()
                                    )
                                ),
                            ),
                        )
                    ),
                    'customer'         => array(
                        'id' => $this->customer->to_relay_id( $order->get_customer_id() )
                    ),
                    'result'           => 'success',
                    'redirect'         => $available_gateways['bacs']->process_payment( $order->get_id() )['redirect'],
                ),
            )
        );

        $this->assertEquals( $expected, $actual );
    }

    public function testCheckoutMutationWithNoAccount() {
        update_option( 'woocommerce_enable_guest_checkout', 'yes' );
        $variable  = $this->variation->create( $this->product->create_variable() );
        $product_ids = array(
            $this->product->create_simple(),
            $this->product->create_simple(),
            $variable['product'],
        );
        $coupon     = new WC_Coupon(
            $this->coupon->create( array( 'product_ids' => $product_ids ) )
        );
        WC()->cart->add_to_cart( $product_ids[0], 3 );
        WC()->cart->add_to_cart( $product_ids[1], 6 );
        WC()->cart->add_to_cart(
			$product_ids[2],
			2,
			$variable['variations'][0],
			array( 'attribute_pa_color' => 'red' )
		);
        WC()->cart->apply_coupon( $coupon->get_code() );

        $input      = array(
            'clientMutationId'   => 'someId',
            'paymentMethod'      => 'bacs',
            'shippingMethod'     => array( 'flat rate' ),
			'billing'            => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
            ),
			'shipping'           => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
            ),
        );

        /**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
        $actual = $this->checkout( $input );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('checkout', $actual['data'] );
        $this->assertArrayHasKey('order', $actual['data']['checkout'] );
        $this->assertArrayHasKey('id', $actual['data']['checkout']['order'] );
        $order = \WC_Order_Factory::get_order( $actual['data']['checkout']['order']['databaseId'] );

        // Get Available payment gateways.
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $expected = array(
            'data' => array(
                'checkout' => array(
                    'clientMutationId' => 'someId',
                    'order'            => array_merge(
                        $this->order->print_query( $order->get_id() ),
                        array(
                            'metaData'      => array(
                                array(
                                    'key'   => 'is_vat_exempt',
                                    'value' => 'no',
                                ),
                            ),
                            'couponLines'   => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId'  => $item->get_id(),
                                                'orderId'     => $item->get_order_id(),
                                                'code'        => $item->get_code(),
                                                'discount'    => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
                                                'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
                                                'coupon'      => null,
                                            );
                                        },
                                        $order->get_items( 'coupon' )
                                    )
                                ),
                            ),
                            'feeLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId' => $item->get_id(),
                                                'orderId'    => $item->get_order_id(),
                                                'amount'     => $item->get_amount(),
                                                'name'       => $item->get_name(),
                                                'taxStatus'  => strtoupper( $item->get_tax_status() ),
                                                'total'      => $item->get_total(),
                                                'totalTax'   => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxClass'   => ! empty( $item->get_tax_class() )
                                                    ? WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD',
                                            );
                                        },
                                        $order->get_items( 'fee' )
                                    )
                                ),
                            ),
                            'shippingLines' => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {

                                            return array(
                                                'databaseId'     => $item->get_id(),
                                                'orderId'        => $item->get_order_id(),
                                                'methodTitle'    => $item->get_method_title(),
                                                'total'          => $item->get_total(),
                                                'totalTax'       => !empty( $item->get_total_tax() )
                                                    ? $item->get_total_tax()
                                                    : null,
                                                'taxClass'       => ! empty( $item->get_tax_class() )
                                                    ? $item->get_tax_class() === 'inherit'
                                                        ? WPEnumType::get_safe_name( 'inherit cart' )
                                                        : WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD'
                                            );
                                        },
                                        $order->get_items( 'shipping' )
                                    )
                                ),
                            ),
                            'taxLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'rateCode'         => $item->get_rate_code(),
                                                'label'            => $item->get_label(),
                                                'taxTotal'         => $item->get_tax_total(),
                                                'shippingTaxTotal' => $item->get_shipping_tax_total(),
                                                'isCompound'       => $item->is_compound(),
                                                'taxRate'          => array( 'databaseId' => $item->get_rate_id() ),
                                            );
                                        },
                                        $order->get_items( 'tax' )
                                    )
                                ),
                            ),
                            'lineItems'     => array(
                                'nodes' => array_values(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'productId'     => $item->get_product_id(),
                                                'variationId'   => ! empty( $item->get_variation_id() )
                                                    ? $item->get_variation_id()
                                                    : null,
                                                'quantity'      => $item->get_quantity(),
                                                'taxClass'      => ! empty( $item->get_tax_class() )
                                                    ? strtoupper( $item->get_tax_class() )
                                                    : 'STANDARD',
                                                'subtotal'      => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
                                                'subtotalTax'   => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
                                                'total'         => ! empty( $item->get_total() ) ? $item->get_total() : null,
                                                'totalTax'      => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxStatus'     => strtoupper( $item->get_tax_status() ),
                                                'product'       => array( 'id' => $this->product->to_relay_id( $item->get_product_id() ) ),
                                                'variation'     => ! empty( $item->get_variation_id() )
                                                    ? array(
                                                        'id' => $this->variation->to_relay_id( $item->get_variation_id() )
                                                    )
                                                    : null,
                                            );
                                        },
                                        $order->get_items()
                                    )
                                ),
                            ),
                        )
                    ),
                    'customer'         => null,
                    'result'           => 'success',
                    'redirect'         => $available_gateways['bacs']->process_payment( $order->get_id() )['redirect'],
                ),
            )
        );

        $this->assertEquals( $expected, $actual );
    }

    public function testCheckoutMutationWithPrepaidOrder() {
        update_option( 'woocommerce_enable_guest_checkout', 'yes' );
        $product_ids = array(
            $this->product->create_simple(
                array(
                    'virtual' => true,
                    'downloadable' => true,
                )
            ),
            $this->product->create_simple(
                array(
                    'virtual' => true,
                    'downloadable' => true,
                )
            ),
        );
        $coupon     = new WC_Coupon(
            $this->coupon->create( array( 'product_ids' => $product_ids ) )
        );
        WC()->cart->add_to_cart( $product_ids[0], 3 );
        WC()->cart->add_to_cart( $product_ids[1], 6 );
        WC()->cart->apply_coupon( $coupon->get_code() );

        $input      = array(
            'clientMutationId'   => 'someId',
            'paymentMethod'      => 'bacs',
            'isPaid'             => true,
            'transactionId'      => 'transaction_id',
            'shippingMethod'     => array( 'flat rate' ),
			'billing'            => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
            ),
			'shipping'           => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
            ),
        );

        /**
		 * Assertion One
		 *
		 * Test mutation and input.
		 */
        $actual = $this->checkout( $input );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('checkout', $actual['data'] );
        $this->assertArrayHasKey('order', $actual['data']['checkout'] );
        $this->assertArrayHasKey('id', $actual['data']['checkout']['order'] );
        $this->assertEquals('COMPLETED', $actual['data']['checkout']['order']['status'] );
        $order = \WC_Order_Factory::get_order( $actual['data']['checkout']['order']['databaseId'] );

        // Get Available payment gateways.
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $expected = array(
            'data' => array(
                'checkout' => array(
                    'clientMutationId' => 'someId',
                    'order'            => array_merge(
                        $this->order->print_query( $order->get_id() ),
                        array(
                            'metaData'      => array(
                                array(
                                    'key'   => 'is_vat_exempt',
                                    'value' => 'no',
                                ),
                            ),
                            'couponLines'   => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId'  => $item->get_id(),
                                                'orderId'     => $item->get_order_id(),
                                                'code'        => $item->get_code(),
                                                'discount'    => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
                                                'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
                                                'coupon'      => null,
                                            );
                                        },
                                        $order->get_items( 'coupon' )
                                    )
                                ),
                            ),
                            'feeLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'databaseId' => $item->get_id(),
                                                'orderId'    => $item->get_order_id(),
                                                'amount'     => $item->get_amount(),
                                                'name'       => $item->get_name(),
                                                'taxStatus'  => strtoupper( $item->get_tax_status() ),
                                                'total'      => $item->get_total(),
                                                'totalTax'   => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxClass'   => ! empty( $item->get_tax_class() )
                                                    ? WPEnumType::get_safe_name( $item->get_tax_class() )
                                                    : 'STANDARD',
                                            );
                                        },
                                        $order->get_items( 'fee' )
                                    )
                                ),
                            ),
                            'shippingLines' => null,
                            'taxLines'      => array(
                                'nodes' => array_reverse(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'rateCode'         => $item->get_rate_code(),
                                                'label'            => $item->get_label(),
                                                'taxTotal'         => $item->get_tax_total(),
                                                'shippingTaxTotal' => $item->get_shipping_tax_total(),
                                                'isCompound'       => $item->is_compound(),
                                                'taxRate'          => array( 'databaseId' => $item->get_rate_id() ),
                                            );
                                        },
                                        $order->get_items( 'tax' )
                                    )
                                ),
                            ),
                            'lineItems'     => array(
                                'nodes' => array_values(
                                    array_map(
                                        function( $item ) {
                                            return array(
                                                'productId'     => $item->get_product_id(),
                                                'variationId'   => ! empty( $item->get_variation_id() )
                                                    ? $item->get_variation_id()
                                                    : null,
                                                'quantity'      => $item->get_quantity(),
                                                'taxClass'      => ! empty( $item->get_tax_class() )
                                                    ? strtoupper( $item->get_tax_class() )
                                                    : 'STANDARD',
                                                'subtotal'      => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
                                                'subtotalTax'   => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
                                                'total'         => ! empty( $item->get_total() ) ? $item->get_total() : null,
                                                'totalTax'      => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                                'taxStatus'     => strtoupper( $item->get_tax_status() ),
                                                'product'       => array( 'id' => $this->product->to_relay_id( $item->get_product_id() ) ),
                                                'variation'     => ! empty( $item->get_variation_id() )
                                                    ? array(
                                                        'id' => $this->variation->to_relay_id( $item->get_variation_id() )
                                                    )
                                                    : null,
                                            );
                                        },
                                        $order->get_items()
                                    )
                                ),
                            ),
                        )
                    ),
                    'customer'         => null,
                    'result'           => 'success',
                    'redirect'         => $available_gateways['bacs']->process_payment( $order->get_id() )['redirect'],
                ),
            )
        );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * Returns a new Stripe Customer object
     *
     * @param string $email  Customer email
     *
     * @return array
     */
    private function create_stripe_customer( $email ) {
        $customer = \Stripe\Customer::create( array( 'email' => $email ) );

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
            array( 'source' => 'tok_visa')
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
            array(
                'amount'               => $amount,
                'currency'             => 'gbp',
                'payment_method_types' => ['card'],
                'customer'             => $customer['id'],
                'payment_method'       => $customer['invoice_settings']['default_payment_method'],
            )
        );

        // use --debug flag to view.
        codecept_debug( $payment_intent );

        return $payment_intent;
    }

    public function testCheckoutMutationWithStripe() {
        update_option( 'woocommerce_enable_guest_checkout', 'yes' );
        // Add items to the cart.
        $product_ids = array(
            $this->product->create_simple(),
            $this->product->create_simple(),
        );
        WC()->cart->add_to_cart( $product_ids[0], 3 );
        WC()->cart->add_to_cart( $product_ids[1], 6 );

        $amount = (int) floatval( WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax() ) * 100;

		try {
			$stripe_customer = $this->create_stripe_customer( 'superfreak500@gmail.com' );
			$stripe_source = $this->create_stripe_source( $stripe_customer );
			$payment_intent = $this->create_stripe_payment_intent( $amount, $stripe_customer );
		} catch ( \Stripe\Exception\AuthenticationException $e ) {
			$this->markTestSkipped( $e->getMessage() );
		}

        $input      = array(
            'clientMutationId'   => 'someId',
            'paymentMethod'      => 'stripe',
            'shippingMethod'     => 'flat rate',
			'billing'            => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
            ),
            'metaData'           => array(
                array(
                    'key'   => '_stripe_source_id',
                    'value' => $stripe_source['id'],
                ),
                array(
                    'key'   => '_stripe_customer_id',
                    'value' => $stripe_customer['id'],
                ),
                array(
                    'key'   => '_stripe_intent_id',
                    'value' => $payment_intent['id'],
                ),
            )
        );

        // Remove "metaData" value field and "redirect" link from the mutation output.
        $modified_mutation = '
            mutation checkout( $input: CheckoutInput! ) {
                checkout( input: $input ) {
                    clientMutationId
                    order {
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
                                    ... on SimpleProduct {
                                        id
                                    }
                                    ... on VariableProduct {
                                        id
                                    }
                                }
                                variation {
                                    id
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
        $actual = $this->checkout( $input, $modified_mutation );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('checkout', $actual['data'] );
        $this->assertArrayHasKey('order', $actual['data']['checkout'] );
        $this->assertArrayHasKey('databaseId', $actual['data']['checkout']['order'] );
        $order = \WC_Order_Factory::get_order( $actual['data']['checkout']['order']['databaseId'] );

        // Get Available payment gateways.
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $expected = array(
            'data' => array(
                'checkout' => array(
                    'clientMutationId' => 'someId',
                    'order'            => array(
                        'databaseId'    => $order->get_id(),
                        'metaData'      => array(
                            array( 'key' => 'is_vat_exempt', ),
                            array( 'key' => '_stripe_source_id' ),
                            array( 'key' => '_stripe_customer_id' ),
                            array( 'key' => '_stripe_intent_id' ),
                            array( 'key' => '_stripe_charge_captured' ),
                            array( 'key' => '_stripe_fee' ),
                            array( 'key' => '_stripe_net' ),
                            array( 'key' => '_stripe_currency' ),
                        ),
                        'lineItems'     => array(
                            'nodes' => array_values(
                                array_map(
                                    function( $item ) {
                                        return array(
                                            'productId'     => $item->get_product_id(),
                                            'variationId'   => ! empty( $item->get_variation_id() )
                                                ? $item->get_variation_id()
                                                : null,
                                            'quantity'      => $item->get_quantity(),
                                            'taxClass'      => ! empty( $item->get_tax_class() )
                                                ? strtoupper( $item->get_tax_class() )
                                                : 'STANDARD',
                                            'subtotal'      => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
                                            'subtotalTax'   => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
                                            'total'         => ! empty( $item->get_total() ) ? $item->get_total() : null,
                                            'totalTax'      => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                            'taxStatus'     => strtoupper( $item->get_tax_status() ),
                                            'product'       => array( 'id' => $this->product->to_relay_id( $item->get_product_id() ) ),
                                            'variation'     => ! empty( $item->get_variation_id() )
                                                ? array(
                                                    'id' => $this->variation->to_relay_id( $item->get_variation_id() )
                                                )
                                                : null,
                                        );
                                    },
                                    $order->get_items()
                                )
                            ),
                        ),
                    ),
                    'result'           => 'success',
                ),
            )
        );

        $this->assertEquals( $expected, $actual );
	}

	public function testCheckoutMutationCartItemValidation() {
		add_filter( 'woocommerce_hold_stock_for_checkout', '__return_false' );

		$product_id = $this->product->create_simple(
			array(
				'manage_stock'   => true,
				'stock_quantity' => 3,
			)
		);

		$key = WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->set_quantity( $key, 5 );

        /**
		 * Assertion One
		 *
		 * Ensure that checkout failed when stock is too low.
		 */
		$input      = array(
            'clientMutationId' => 'someId',
            'paymentMethod'    => 'bacs',
            'shippingMethod'   => array( 'flat rate' ),
			'billing'          => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'company'   => 'Harris Teeter',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
            ),
            'account'          => array(
                'username' => 'test_user_1',
                'password' => 'test_pass'
            )
		);
        $failed = $this->checkout( $input );

        // use --debug flag to view.
		codecept_debug( $failed );

		$this->assertArrayHasKey( 'errors', $failed );
	}
}
