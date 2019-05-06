<?php

class CartMutationsTest extends \Codeception\TestCase\WPTestCase {
    private $customer;
    private $coupon;
    private $product;
    private $variation;

    public function setUp() {
        parent::setUp();

        $this->customer  = $this->getModule('\Helper\Wpunit')->customer();
        $this->coupon    = $this->getModule('\Helper\Wpunit')->coupon();
        $this->product   = $this->getModule('\Helper\Wpunit')->product();
        $this->variation = $this->getModule('\Helper\Wpunit')->product_variation();
    }

    public function tearDown() {
        \WC()->cart->empty_cart();

        parent::tearDown();
    }

    private function addToCart( $input ) {
        $mutation   = '
            mutation addToCart( $input: AddToCartInput! ) {
                addToCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
                            id
                        }
                        variation {
                            id
                        }
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
        ';

        $variables = array( 'input' => $input  );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'addToCart',
                'variables'      => $variables,
            )
        );

        return $actual;
    }

    // tests
    public function testAddToCartMutationWithProduct() {
        $product_id = $this->product->create_simple();
        $actual     = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_id,
                'quantity'         => 2,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('addToCart', $actual['data'] );
        $this->assertArrayHasKey('cartItem', $actual['data']['addToCart'] );
        $this->assertArrayHasKey('key', $actual['data']['addToCart']['cartItem'] );
        $key = $actual['data']['addToCart']['cartItem']['key'];

        // Get newly created cart item data.
        $cart = WC()->cart;
        $cart_item = $cart->get_cart_item( $key );
        $this->assertNotEmpty( $cart_item );
        
        // Check cart item data.
		$expected = array(
			'data' => array(
				'addToCart' => array(
					'clientMutationId' => 'someId',
					'cartItem'         => array(
                        'key'          => $cart_item['key'],
                        'product'      => array(
                            'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                        ),
                        'variation'    => null,
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                        'total'        => floatval( $cart_item['line_total'] ),
                        'tax'          => floatval( $cart_item['line_tax'] ),
					),
				),
			),
		);
		$this->assertEqualSets( $expected, $actual );
    }

    public function testAddToCartMutationWithProductVariation() {
        $ids    = $this->variation->create( $this->product->create_variable() );
        $actual = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 3,
                'variationId'      => $ids['variations'][0],
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );
        
        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('addToCart', $actual['data'] );
        $this->assertArrayHasKey('cartItem', $actual['data']['addToCart'] );
        $this->assertArrayHasKey('key', $actual['data']['addToCart']['cartItem'] );
        $key = $actual['data']['addToCart']['cartItem']['key'];

        // Get newly created cart item data.
        $cart = WC()->cart;
        $cart_item = $cart->get_cart_item( $key );
        $this->assertNotEmpty( $cart_item );

        $expected = array(
            'data' => array(
                'addToCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItem'         => array(
                        'key'          => $cart_item['key'],
                        'product'      => array(
                            'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                        ),
                        'variation'    => array(
                            'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
                        ),
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                        'total'        => floatval( $cart_item['line_total'] ),
                        'tax'          => floatval( $cart_item['line_tax'] ),
                    ),
                ),
            ),
        );
        $this->assertEqualSets( $expected, $actual );
    }

    public function testRemoveItemFromCartMutation() {
        $ids  = $this->variation->create( $this->product->create_variable() );
        $cart = WC()->cart;
        $cart_item = $cart->get_cart_item(
            $cart->add_to_cart( $ids['product'], 2, $ids['variations'][0] )
        );

        $mutation   = '
            mutation removeItemFromCart( $input: RemoveItemFromCartInput! ) {
                removeItemFromCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
                            id
                        }
                        variation {
                            id
                        }
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
        ';

        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'key'              => $cart_item['key'],
            ),
        );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'removeItemFromCart',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'removeItemFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItem'         => array(
                        'key'          => $cart_item['key'],
                        'product'      => array(
                            'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                        ),
                        'variation'    => array(
                            'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
                        ),
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                        'total'        => floatval( $cart_item['line_total'] ),
                        'tax'          => floatval( $cart_item['line_tax'] ),
                    ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertEmpty( \WC()->cart->get_cart_item( $cart_item['key'] ) );
    }

    public function testRestoreCartItemMutation() {
        $cart = WC()->cart;

        // Create products.
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Create cart item.
        $cart_item = $cart->get_cart_item(
            $cart->add_to_cart( $ids['product'], 2, $ids['variations'][0] )
        );

        // Remove cart item.
        $cart->remove_cart_item( $cart_item['key'] );

        $mutation = '
            mutation restoreCartItem( $input: RestoreCartItemInput! ) {
                restoreCartItem( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
                            id
                        }
                        variation {
                            id
                        }
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
        ';

        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'key'              => $cart_item['key'],
            ),
        );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'restoreCartItem',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'restoreCartItem' => array(
                    'clientMutationId' => 'someId',
                    'cartItem'         => array(
                        'key'          => $cart_item['key'],
                        'product'      => array(
                            'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                        ),
                        'variation'    => array(
                            'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
                        ),
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                        'total'        => floatval( $cart_item['line_total'] ),
                        'tax'          => floatval( $cart_item['line_tax'] ),
                    ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertNotEmpty( \WC()->cart->get_cart_item( $cart_item['key'] ) );
    }

    public function testEmptyCartMutation() {
        $cart = WC()->cart;

        // Create products.
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Add items to carts.
        $cart_item = $cart->get_cart_item(
            $cart->add_to_cart( $ids['product'], 2, $ids['variations'][0] )
        );

        $mutation = '
            mutation emptyCart( $input: EmptyCartInput! ) {
                emptyCart( input: $input ) {
                    clientMutationId
                    cart {
                        contents {
                            nodes {
                                key
                                product {
                                    id
                                }
                                variation {
                                    id
                                }
                                quantity
                                subtotal
                                subtotalTax
                                total
                                tax
                            }
                        }
                    }
                }
            }
        ';

        $variables = array(
            'input' => array( 'clientMutationId' => 'someId' ),
        );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'emptyCart',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'emptyCart' => array(
                    'clientMutationId' => 'someId',
                    'cart'         => array(
                        'contents' => array(
                            'nodes' => array(
                                array(
                                    'key'          => $cart_item['key'],
                                    'product'      => array(
                                        'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                                    ),
                                    'variation'    => array(
                                        'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
                                    ),
                                    'quantity'     => $cart_item['quantity'],
                                    'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                                    'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                                    'total'        => floatval( $cart_item['line_total'] ),
                                    'tax'          => floatval( $cart_item['line_tax'] ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertTrue( \WC()->cart->is_empty() );
    }

    public function testApplyCouponMutation() {
        $cart = WC()->cart;

        // Create products.
        $product_id = $this->product->create_simple();

        // Create coupon.
        $coupon_code = wc_get_coupon_code_by_id(
            $this->coupon->create(
                array( 'product_ids' => array( $product_id ) )
            )
        );

        // Add items to carts.
        $cart_item_key = $cart->add_to_cart( $product_id, 1 );

        $old_total = \WC()->cart->get_cart_contents_total();

        $mutation = '
            mutation applyCoupon( $input: ApplyCouponInput! ) {
                applyCoupon( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
                            nodes {
                                code
                            }
                        }
                        contents {
                            nodes {
                                key
                                product {
                                    id
                                }
                                quantity
                                subtotal
                                subtotalTax
                                total
                                tax
                            }
                        }
                    }
                }
            }
        ';

        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'code'             => $coupon_code,
            ),
        );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'applyCoupon',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        // Get updated cart item.
        $cart_item = WC()->cart->get_cart_item( $cart_item_key );

        $expected = array(
            'data' => array(
                'applyCoupon' => array(
                    'clientMutationId' => 'someId',
                    'cart'         => array(
                        'appliedCoupons' => array(
                            'nodes' => array(
                                array(
                                    'code' => $coupon_code,
                                ),
                            ),
                        ),
                        'contents' => array(
                            'nodes' => array(
                                array(
                                    'key'          => $cart_item['key'],
                                    'product'      => array(
                                        'id' => $this->product->to_relay_id( $cart_item['product_id'] ),
                                    ),
                                    'quantity'     => $cart_item['quantity'],
                                    'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                                    'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                                    'total'        => floatval( $cart_item['line_total'] ),
                                    'tax'          => floatval( $cart_item['line_tax'] ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );


        $new_total = \WC()->cart->get_cart_contents_total();

        // Use --debug to view.
        codecept_debug( array( 'old' => $old_total, 'new' => $new_total ) );

        $this->assertTrue( $old_total > $new_total );
    }
}