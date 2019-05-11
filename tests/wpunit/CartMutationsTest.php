<?php

class CartMutationsTest extends \Codeception\TestCase\WPTestCase {
    private $shop_manager;
    private $customer;
    private $coupon;
    private $product;
    private $variation;
    private $cart;

    public function setUp() {
        parent::setUp();

        $this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
        $this->customer     = $this->getModule('\Helper\Wpunit')->customer();
        $this->coupon       = $this->getModule('\Helper\Wpunit')->coupon();
        $this->product      = $this->getModule('\Helper\Wpunit')->product();
        $this->variation    = $this->getModule('\Helper\Wpunit')->product_variation();
        $this->cart         = $this->getModule('\Helper\Wpunit')->cart();
    }

    public function tearDown() {
        \WC()->cart->empty_cart();

        parent::tearDown();
    }

    private function addToCart( $input ) {
        $mutation = '
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

        $actual = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'addToCart',
                'variables'      => array( 'input' => $input  ),
            )
        );

        return $actual;
    }

    private function removeItemsFromCart( $input ) {
        $mutation = '
            mutation removeItemsFromCart( $input: RemoveItemsFromCartInput! ) {
                removeItemsFromCart( input: $input ) {
                    clientMutationId
                    cartItems {
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

        $actual = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'removeItemsFromCart',
                'variables'      => array( 'input' => $input  ),
            )
        );

        return $actual;
    }

    private function restoreItems( $input ) {
        $mutation = '
            mutation restoreCartItems( $input: RestoreCartItemsInput! ) {
                restoreCartItems( input: $input ) {
                    clientMutationId
                    cartItems {
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

        $actual = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'restoreCartItems',
                'variables'      => array( 'input' => $input  ),
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

    public function testUpdateCartItemQuantityMutation() {
        $product_id = $this->product->create_simple();
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_id,
                'quantity'         => 2,
            )
        );

        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $this->assertArrayHasKey('key', $addToCart['data']['addToCart']['cartItem'] );
        $key = $addToCart['data']['addToCart']['cartItem']['key'];

        $mutation = '
            mutation updateItemQuantity( $input: UpdateItemQuantityInput! ) {
                updateItemQuantity( input: $input ) {
                    clientMutationId
                    cartItem {
                        quantity
                    }
                }
            }
        ';

        $actual = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'updateItemQuantity',
                'variables'      => array(
                    'input' => array(
                        'clientMutationId' => 'someId',
                        'key'              => $key,
                        'quantity'         => 4,
                    )
                ),
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );
        
        // Check cart item data.
		$expected = array(
			'data' => array(
				'updateItemQuantity' => array(
					'clientMutationId' => 'someId',
					'cartItem'         => array(
                        'quantity'     => 4,
					),
				),
			),
		);
		$this->assertEqualSets( $expected, $actual );
    }

    public function testRemoveItemsFromCartMutation() {
        $ids  = $this->variation->create( $this->product->create_variable() );
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 2,
                'variationId'      => $ids['variations'][0],
            )
        );

        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem = $addToCart['data']['addToCart']['cartItem'];
        $key = $cartItem['key'];

        $actual = $this->removeItemsFromCart(
            array(
                'clientMutationId' => 'someId',
                'keys'             => array( $key ),
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'removeItemsFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'         => array( $cartItem ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertEmpty( \WC()->cart->get_cart_item( $key ) );
    }

    public function testRemoveItemsFromCartMutationWithMultipleItems() {
        // Create products
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Add item 1.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 2,
                'variationId'      => $ids['variations'][0],
            )
        );

        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem1 = $addToCart['data']['addToCart']['cartItem'];
        $key1 = $cartItem1['key'];

        // Add item 2.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 3,
                'variationId'      => $ids['variations'][1],
            )
        );

        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem2 = $addToCart['data']['addToCart']['cartItem'];
        $key2 = $cartItem2['key'];

        $actual = $this->removeItemsFromCart(
            array(
                'clientMutationId' => 'someId',
                'keys'             => array( $key1, $key2 ),
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'removeItemsFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'         => array( $cartItem1, $cartItem2 ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertEmpty( \WC()->cart->get_cart_item( $key1 ) );
        $this->assertEmpty( \WC()->cart->get_cart_item( $key2 ) );
    }

    public function testRemoveItemsFromCartMutationUsingAllField() {
        // Create products
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Add item 1.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 2,
                'variationId'      => $ids['variations'][0],
            )
        );

        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem1 = $addToCart['data']['addToCart']['cartItem'];
        $key1 = $cartItem1['key'];

        // Add item 2.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 3,
                'variationId'      => $ids['variations'][1],
            )
        );

        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem2 = $addToCart['data']['addToCart']['cartItem'];
        $key2 = $cartItem2['key'];

        $actual = $this->removeItemsFromCart(
            array(
                'clientMutationId' => 'someId',
                'all'              => true
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'removeItemsFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'         => array( $cartItem1, $cartItem2 ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertTrue( \WC()->cart->is_empty() );
    }

    public function testRestoreCartItemsMutation() {
        // Create products
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Add item.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 2,
                'variationId'      => $ids['variations'][0],
            )
        );

        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem = $addToCart['data']['addToCart']['cartItem'];
        $key = $cartItem['key'];

        // Remove item.
        $this->removeItemsFromCart(
            array(
                'clientMutationId' => 'someId',
                'all'              => true
            )
        );

        $actual = $this->restoreItems(
            array(
                'clientMutationId' => 'someId',
                'keys'             => array( $key ),
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'restoreCartItems' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'        => array( $cartItem ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertNotEmpty( \WC()->cart->get_cart_item( $key ) );
    }

    public function testRestoreCartItemsMutationWithMultipleItems() {
        // Create products
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Add item 1.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 2,
                'variationId'      => $ids['variations'][0],
            )
        );

        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem1 = $addToCart['data']['addToCart']['cartItem'];
        $key1 = $cartItem1['key'];

        // Add item 2.
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 1,
                'variationId'      => $ids['variations'][1],
            )
        );

        $this->assertArrayHasKey('data', $addToCart );
        $this->assertArrayHasKey('addToCart', $addToCart['data'] );
        $this->assertArrayHasKey('cartItem', $addToCart['data']['addToCart'] );
        $cartItem2 = $addToCart['data']['addToCart']['cartItem'];
        $key2 = $cartItem2['key'];

        // Remove items.
        $this->removeItemsFromCart(
            array(
                'clientMutationId' => 'someId',
                'all'              => true
            )
        );

        $actual = $this->restoreItems(
            array(
                'clientMutationId' => 'someId',
                'keys'             => array( $key1, $key2 ),
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'restoreCartItems' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'        => array( $cartItem1, $cartItem2 ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
        $this->assertNotEmpty( \WC()->cart->get_cart_item( $key1 ) );
        $this->assertNotEmpty( \WC()->cart->get_cart_item( $key2 ) );
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

    public function testRemoveCouponMutation() {
        $cart = WC()->cart;

        // Create product and coupon.
        $product_id  = $this->product->create_simple();
        $coupon_code = wc_get_coupon_code_by_id(
            $this->coupon->create(
                array( 'product_ids' => array( $product_id ) )
            )
        );

        // Add item and coupon to cart and get total..
        $cart_item_key = $cart->add_to_cart( $product_id, 3 );
        $cart->apply_coupon( $coupon_code );

        $mutation = '
            mutation removeCoupons( $input: RemoveCouponsInput! ) {
                removeCoupons( input: $input ) {
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
                'codes'            => array( $coupon_code ),
            ),
        );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'removeCoupons',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        // Get updated cart item.
        $cart_item = \WC()->cart->get_cart_item( $cart_item_key );

        $expected = array(
            'data' => array(
                'removeCoupons' => array(
                    'clientMutationId' => 'someId',
                    'cart'         => array(
                        'appliedCoupons' => array(
                            'nodes' => array(),
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
    }

    public function testAddFeeMutation() {
        $cart = WC()->cart;

        // Create product and coupon.
        $product_id  = $this->product->create_simple();
        $coupon_code = wc_get_coupon_code_by_id(
            $this->coupon->create(
                array( 'product_ids' => array( $product_id ) )
            )
        );

        // Add item and coupon to cart.
        $cart->add_to_cart( $product_id, 3 );
        $cart->apply_coupon( $coupon_code );

        $mutation = '
            mutation addFee( $input: AddFeeInput! ) {
                addFee( input: $input ) {
                    clientMutationId
                    cartFee {
                        id
                        name
                        taxClass
                        taxable
                        amount
                        total
                    }
                }
            }
        ';

        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'name'             => 'extra_fee',
                'amount'           => 49.99,
            ),
        );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'addFee',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('errors', $actual );

        wp_set_current_user( $this->shop_manager );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'addFee',
                'variables'      => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $expected = array(
            'data' => array(
                'addFee' => array(
                    'clientMutationId' => 'someId',
                    'cartFee'          => $this->cart->print_fee_query( 'extra_fee' ),
                ),
            ),
        );

        $this->assertEqualSets( $expected, $actual );
    }
}