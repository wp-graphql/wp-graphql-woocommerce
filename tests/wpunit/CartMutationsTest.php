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
        \WC()->cart->empty_cart( true );

        parent::tearDown();
	}

	private function graphql( $query, $operation_name = null, $variables = null ) {
		// Run GraphQL request.
		$results = graphql( compact( 'query', 'operation_name', 'variables' ) );

		// use --debug flag to view.
		codecept_debug( $results );

        return $results;
	}

    private function addToCart( $input ) {
        $mutation = '
            mutation addToCart( $input: AddToCartInput! ) {
                addToCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
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
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
        ';

        return $this->graphql( $mutation, 'addToCart', compact( 'input' ) );
    }

    private function removeItemsFromCart( $input ) {
        $mutation = '
            mutation removeItemsFromCart( $input: RemoveItemsFromCartInput! ) {
                removeItemsFromCart( input: $input ) {
                    clientMutationId
                    cartItems {
                        key
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
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
        ';

		return $this->graphql( $mutation, 'removeItemsFromCart', compact( 'input' ) );
    }

    private function restoreItems( $input ) {
        $mutation = '
            mutation restoreCartItems( $input: RestoreCartItemsInput! ) {
                restoreCartItems( input: $input ) {
                    clientMutationId
                    cartItems {
                        key
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
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
		';

		return $this->graphql( $mutation, 'restoreCartItems', compact( 'input' ) );
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
							'node' => array(
								'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
							),
                        ),
                        'variation'    => null,
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => wc_graphql_price( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
                        'total'        => wc_graphql_price( $cart_item['line_total'] ),
                        'tax'          => wc_graphql_price( $cart_item['line_tax'] ),
					),
				),
			),
		);
		$this->assertEquals( $expected, $actual );
    }

    public function testAddToCartMutationWithProductVariation() {
        $ids    = $this->variation->create( $this->product->create_variable() );
        $actual = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 3,
				'variationId'      => $ids['variations'][0],
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
            )
        );

        // Retrieve cart item key.
        $this->assertArrayHasKey( 'data', $actual );
        $this->assertArrayHasKey( 'addToCart', $actual['data'] );
        $this->assertArrayHasKey( 'cartItem', $actual['data']['addToCart'] );
        $this->assertArrayHasKey( 'key', $actual['data']['addToCart']['cartItem'] );
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
							'node' => array(
								'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
							),
                        ),
                        'variation'    => array(
							'node' => array(
								'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
							),
                        ),
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => wc_graphql_price( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
                        'total'        => wc_graphql_price( $cart_item['line_total'] ),
                        'tax'          => wc_graphql_price( $cart_item['line_tax'] ),
                    ),
                ),
            ),
        );
        $this->assertEquals( $expected, $actual );
    }

    public function testUpdateCartItemQuantitiesMutation() {
        // Create products.
        $product_1 = $this->product->create_simple();
        $product_2 = $this->product->create_simple();
        $product_3 = $this->product->create_simple();

        // Add items to cart and retrieve keys
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_1,
                'quantity'         => 2,
            )
        );
        $this->assertArrayHasKey('data', $addToCart );
        $key_1 = $addToCart['data']['addToCart']['cartItem']['key'];
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_2,
                'quantity'         => 5,
            )
        );
        $this->assertArrayHasKey('data', $addToCart );
        $key_2 = $addToCart['data']['addToCart']['cartItem']['key'];
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_3,
                'quantity'         => 1,
            )
        );
        $this->assertArrayHasKey('data', $addToCart );
        $key_3 = $addToCart['data']['addToCart']['cartItem']['key'];

        // Update items mutation.
        $mutation = '
            mutation updateItemQuantities( $input: UpdateItemQuantitiesInput! ) {
                updateItemQuantities( input: $input ) {
                    clientMutationId
                    updated {
                        key
                        quantity
                    }
                    removed {
                        key
                        quantity
                    }
                    items {
                        key
                        quantity
                    }
                }
            }
        ';

        $actual = $this->graphql(
			$mutation,
			'updateItemQuantities',
			array(
				'input' => array(
					'clientMutationId' => 'someId',
					'items'            => array(
						array( 'key' => $key_1, 'quantity' => 4 ),
						array( 'key' => $key_2, 'quantity' => 2 ),
						array( 'key' => $key_3, 'quantity' => 0 ),
					),
				),
			)
        );

        // Check cart item data.
		$expected = array(
			'data' => array(
				'updateItemQuantities' => array(
					'clientMutationId' => 'someId',
					'updated' => array(
                        array( 'key' => $key_1, 'quantity' => 4 ),
                        array( 'key' => $key_2, 'quantity' => 2 ),
                    ),
                    'removed' => array(
                        array( 'key' => $key_3, 'quantity' => 1 ),
                    ),
                    'items'   => array(
                        array( 'key' => $key_1, 'quantity' => 4 ),
                        array( 'key' => $key_2, 'quantity' => 2 ),
                        array( 'key' => $key_3, 'quantity' => 1 ),
                    )
				),
			),
		);
		$this->assertEquals( $expected, $actual );
    }

    public function testRemoveItemsFromCartMutation() {
        $ids  = $this->variation->create( $this->product->create_variable() );
        $addToCart = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 2,
				'variationId'      => $ids['variations'][0],
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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

        $expected = array(
            'data' => array(
                'removeItemsFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'         => array( $cartItem ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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

        $expected = array(
            'data' => array(
                'removeItemsFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'         => array( $cartItem1, $cartItem2 ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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

        $expected = array(
            'data' => array(
                'removeItemsFromCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'         => array( $cartItem1, $cartItem2 ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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

        $expected = array(
            'data' => array(
                'restoreCartItems' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'        => array( $cartItem ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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
				'variation'        => array(
					array(
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					),
				),
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

        $expected = array(
            'data' => array(
                'restoreCartItems' => array(
                    'clientMutationId' => 'someId',
                    'cartItems'        => array( $cartItem1, $cartItem2 ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
        $this->assertNotEmpty( \WC()->cart->get_cart_item( $key1 ) );
        $this->assertNotEmpty( \WC()->cart->get_cart_item( $key2 ) );
    }

    public function testEmptyCartMutation() {
        $cart = WC()->cart;

        // Create products.
        $ids  = $this->variation->create( $this->product->create_variable() );

        // Add items to carts.
        $cart_item = $cart->get_cart_item(
            $cart->add_to_cart(
				$ids['product'],
				2,
				$ids['variations'][0],
				array( 'attribute_pa_color' => 'red' )
			)
        );

        $mutation = '
            mutation emptyCart( $input: EmptyCartInput! ) {
                emptyCart( input: $input ) {
                    clientMutationId
                    deletedCart {
                        contents {
                            nodes {
                                key
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
        $actual    = $this->graphql( $mutation, 'emptyCart', $variables );

        $expected = array(
            'data' => array(
                'emptyCart' => array(
                    'clientMutationId' => 'someId',
                    'deletedCart'         => array(
                        'contents' => array(
                            'nodes' => array(
                                array(
                                    'key'          => $cart_item['key'],
                                    'product'      => array(
										'node' => array(
											'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
										),
                                    ),
                                    'variation'    => array(
										'node' => array(
											'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
										),
                                    ),
                                    'quantity'     => $cart_item['quantity'],
                                    'subtotal'     => wc_graphql_price( $cart_item['line_subtotal'] ),
                                    'subtotalTax'  => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
                                    'total'        => wc_graphql_price( $cart_item['line_total'] ),
                                    'tax'          => wc_graphql_price( $cart_item['line_tax'] ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
        $this->assertTrue( \WC()->cart->is_empty() );
    }

    public function testApplyCouponMutation() {
        $cart = WC()->cart;

        // Create products.
        $product_id = $this->product->create_simple(
            array( 'regular_price' => 100 )
        );

        // Create coupon.
        $coupon_code = wc_get_coupon_code_by_id(
            $this->coupon->create(
                array(
                    'amount'      => 0.5,
                    'product_ids' => array( $product_id )
                )
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
                                    node {
                                        id
                                    }
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
        $actual    = $this->graphql( $mutation, 'applyCoupon', $variables );

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
										'node' => array(
											'id' => $this->product->to_relay_id( $cart_item['product_id'] ),
										),
                                    ),
                                    'quantity'     => $cart_item['quantity'],
                                    'subtotal'     => wc_graphql_price( $cart_item['line_subtotal'] ),
                                    'subtotalTax'  => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
                                    'total'        => wc_graphql_price( $cart_item['line_total'] ),
                                    'tax'          => wc_graphql_price( $cart_item['line_tax'] ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );


        $new_total = \WC()->cart->get_cart_contents_total();

        // Use --debug to view.
        codecept_debug( array( 'old' => $old_total, 'new' => $new_total ) );

        $this->assertTrue( $old_total > $new_total );
    }

    public function testApplyCouponMutationWithInvalidCoupons() {
        $cart = WC()->cart;

        // Create products.
        $product_id = $this->product->create_simple();

        // Create invalid coupon codes.
        $coupon_id           = $this->coupon->create(
            array( 'product_ids' => array( $product_id ) )
        );
        $expired_coupon_code = wc_get_coupon_code_by_id(
            $this->coupon->create(
                array(
                    'product_ids'  => array( $product_id ),
                    'date_expires' => time() - 20,
                )
            )
        );
        $applied_coupon_code = wc_get_coupon_code_by_id(
            $this->coupon->create(
                array( 'product_ids' => array( $product_id ) )
            )
        );

        // Add items to carts.
        $cart_item_key = $cart->add_to_cart( $product_id, 1 );
        $cart->apply_coupon( $applied_coupon_code );

        $old_total = \WC()->cart->get_cart_contents_total();

        $mutation = '
            mutation ( $input: ApplyCouponInput! ) {
                applyCoupon( input: $input ) {
                    clientMutationId
                }
            }
        ';

        /**
         * Assertion One
         *
         * Can't pass coupon ID as coupon "code". Mutation should fail.
         */
        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'code'             => "$coupon_id",
            ),
        );
        $actual    = $this->graphql( $mutation, null, $variables );

        $this->assertNotEmpty( $actual['errors'] );
        $this->assertEmpty( $actual['data']['applyCoupon'] );

        /**
         * Assertion Two
         *
         * Can't pass expired coupon code. Mutation should fail.
         */
        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'code'             => $expired_coupon_code,
            ),
        );
        $actual    = $this->graphql( $mutation, null, $variables );

        $this->assertNotEmpty( $actual['errors'] );
        $this->assertEmpty( $actual['data']['applyCoupon'] );

        /**
         * Assertion Three
         *
         * Can't pass coupon already applied to the cart. Mutation should fail.
         */
        $variables = array(
            'input' => array(
                'clientMutationId' => 'someId',
                'code'             => $applied_coupon_code,
            ),
        );
        $actual    = $this->graphql( $mutation, null, $variables );

        $this->assertNotEmpty( $actual['errors'] );
        $this->assertEmpty( $actual['data']['applyCoupon'] );

        $this->assertEquals( $old_total, \WC()->cart->get_cart_contents_total() );
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
                                    node {
                                        id
                                    }
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
        $actual    = $this->graphql( $mutation, 'removeCoupons', $variables );

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
										'node' => array(
											'id' => $this->product->to_relay_id( $cart_item['product_id'] ),
										),
                                    ),
                                    'quantity'     => $cart_item['quantity'],
                                    'subtotal'     => wc_graphql_price( $cart_item['line_subtotal'] ),
                                    'subtotalTax'  => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
                                    'total'        => wc_graphql_price( $cart_item['line_total'] ),
                                    'tax'          => wc_graphql_price( $cart_item['line_tax'] ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
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
        $actual    = $this->graphql( $mutation, 'addFee', $variables );

        $this->assertArrayHasKey('errors', $actual );

        wp_set_current_user( $this->shop_manager );
        $actual = $this->graphql( $mutation, 'addFee', $variables );

        $expected = array(
            'data' => array(
                'addFee' => array(
                    'clientMutationId' => 'someId',
                    'cartFee'          => $this->cart->print_fee_query( 'extra_fee' ),
                ),
            ),
        );

        $this->assertEquals( $expected, $actual );
	}

	public function testAddToCartMutationErrors() {
		// Create products.
        $product_id    = $this->product->create_simple(
			array(
				'manage_stock'   => true,
				'stock_quantity' => 1,
			)
		);
		$variation_ids = $this->variation->create( $this->product->create_variable() );

		$product   = \wc_get_product( $variation_ids['product'] );
		$attribute = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( 'test' );
		$attribute->set_options( array( 'yes', 'no' ) );
		$attribute->set_position( 3 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$attributes = array_values( $product->get_attributes() );
		$attributes[] = $attribute;
		$product->set_attributes( $attributes );
		$product->save();

		\WC()->session->set( 'wc_notices', null );
		$missing_attributes = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $variation_ids['product'],
				'quantity'         => 5,
                'variationId'      => $variation_ids['variations'][0],
            )
		);

		$this->assertArrayHasKey( 'errors', $missing_attributes );

		\WC()->session->set( 'wc_notices', null );
		$not_enough_stock = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_id,
                'quantity'         => 5,
            )
		);

		$this->assertArrayHasKey( 'errors', $not_enough_stock );
	}
}
