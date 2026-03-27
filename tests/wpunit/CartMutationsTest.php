<?php

class CartMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	// tests
	public function testAddToCartMutationWithProduct() {
		$product_id = $this->factory->product->createSimple();

		$query = '
            mutation( $input: AddToCartInput! ) {
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

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'productId'        => $product_id,
				'quantity'         => 2,
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Confirm valid response
		$this->assertResponseIsValid( $response );

		// Get/validate cart item key.
		$cart_item_key = $this->lodashGet( $response, 'data.addToCart.cartItem.key' );
		$this->assertNotEmpty( $cart_item_key );

		// Get cart item data.
		$cart      = \WC()->cart;
		$cart_item = $cart->get_cart_item( $cart_item_key );
		$this->assertNotEmpty( $cart_item );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'addToCart.clientMutationId', 'someId' ),
				$this->expectedField( 'addToCart.cartItem.key', $cart_item_key ),
				$this->expectedField( 'addToCart.cartItem.product.node.id', $this->toRelayId( 'post', $product_id ) ),
				$this->expectedField( 'addToCart.cartItem.quantity', 2 ),
				$this->expectedField( 'addToCart.cartItem.subtotal', wc_graphql_price( $cart_item['line_subtotal'] ) ),
				$this->expectedField( 'addToCart.cartItem.subtotalTax', wc_graphql_price( $cart_item['line_subtotal_tax'] ) ),
				$this->expectedField( 'addToCart.cartItem.total', wc_graphql_price( $cart_item['line_total'] ) ),
				$this->expectedField( 'addToCart.cartItem.tax', wc_graphql_price( $cart_item['line_tax'] ) ),
			]
		);
	}

	public function testAddToCartMutationWithProductVariation() {
		$ids = $this->factory->product_variation->createSome();

		$query = '
			mutation( $input: AddToCartInput! ) {
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

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'productId'        => $ids['product'],
				'quantity'         => 3,
				'variationId'      => $ids['variations'][0],
				'variation'        => [
					[
						'attributeName'  => 'color',
						'attributeValue' => 'red',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Confirm valid response
		$this->assertResponseIsValid( $response );

		// Get/validate cart item key.
		$cart_item_key = $this->lodashGet( $response, 'data.addToCart.cartItem.key' );
		$this->assertNotEmpty( $cart_item_key );

		// Get cart item data.
		$cart      = \WC()->cart;
		$cart_item = $cart->get_cart_item( $cart_item_key );
		$this->assertNotEmpty( $cart_item );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'addToCart.clientMutationId', 'someId' ),
				$this->expectedField( 'addToCart.cartItem.key', $cart_item_key ),
				$this->expectedField( 'addToCart.cartItem.product.node.id', $this->toRelayId( 'post', $ids['product'] ) ),
				$this->expectedField( 'addToCart.cartItem.variation.node.id', $this->toRelayId( 'post', $ids['variations'][0] ) ),
				$this->expectedField( 'addToCart.cartItem.quantity', 3 ),
				$this->expectedField( 'addToCart.cartItem.subtotal', wc_graphql_price( $cart_item['line_subtotal'] ) ),
				$this->expectedField( 'addToCart.cartItem.subtotalTax', wc_graphql_price( $cart_item['line_subtotal_tax'] ) ),
				$this->expectedField( 'addToCart.cartItem.total', wc_graphql_price( $cart_item['line_total'] ) ),
				$this->expectedField( 'addToCart.cartItem.tax', wc_graphql_price( $cart_item['line_tax'] ) ),
			]
		);
	}

	public function testUpdateCartItemQuantitiesMutation() {
		// Create/add some products to the cart.
		$cart_item_data = [
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 2,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 5,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			],
		];

		// Store cart item keys for use in mutation.
		$keys = $this->factory->cart->add( ...$cart_item_data );

		// Define mutation.
		$query = '
            mutation( $input: UpdateItemQuantitiesInput! ) {
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

		// Define variables
		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'items'            => [
					[
						'key'      => $keys[0],
						'quantity' => 4,
					],
					[
						'key'      => $keys[1],
						'quantity' => 2,
					],
					[
						'key'      => $keys[2],
						'quantity' => 0,
					],
				],
			],
		];

		// Execute mutation.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateItemQuantities.clientMutationId', 'someId' ),
				$this->expectedNode(
					'updateItemQuantities.updated',
					[
						'key'      => $keys[0],
						'quantity' => 4,
					]
				),
				$this->expectedNode(
					'updateItemQuantities.updated',
					[
						'key'      => $keys[1],
						'quantity' => 2,
					]
				),
				$this->expectedNode(
					'updateItemQuantities.removed',
					[
						'key'      => $keys[2],
						'quantity' => 1,
					]
				),
				$this->expectedNode(
					'updateItemQuantities.items',
					[
						'key'      => $keys[0],
						'quantity' => 4,
					]
				),
				$this->expectedNode(
					'updateItemQuantities.items',
					[
						'key'      => $keys[1],
						'quantity' => 2,
					]
				),
				$this->expectedNode(
					'updateItemQuantities.items',
					[
						'key'      => $keys[2],
						'quantity' => 1,
					]
				),
			]
		);
	}

	public function testRemoveItemsFromCartMutation() {
		// Create/add some products to the cart.
		$cart_item_data = [
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 2,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 5,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			],
		];

		// Store cart item keys for use in mutation.
		$keys = $this->factory->cart->add( ...$cart_item_data );

		$query = '
			mutation( $input: RemoveItemsFromCartInput! ) {
				removeItemsFromCart( input: $input ) {
					clientMutationId
					cartItems {
						key
					}
				}
			}
		';

		// Define expected response data.
		$expected = [ $this->expectedField( 'removeItemsFromCart.clientMutationId', 'someId' ) ];
		foreach ( $keys as $key ) {
			$expected[] = $this->expectedNode( 'removeItemsFromCart.cartItems', compact( 'key' ) );
		}

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'keys'             => $keys,
			],
		];

		// Execute mutation w/ "keys" array.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm none of the items in cart.
		foreach ( $keys as $key ) {
			$this->assertEmpty(
				\WC()->cart->get_cart_item( $key ),
				"{$key} still in cart after \"removeItemsFromCart\" mutation."
			);
		}

		// Add more items and execute mutation with "all" flag.
		$keys = $this->factory->cart->add( ...$cart_item_data );

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'all'              => true,
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm none of the items in cart.
		foreach ( $keys as $key ) {
			$this->assertEmpty(
				\WC()->cart->get_cart_item( $key ),
				"{$key} still in cart after \"removeItemsFromCart\" mutation with \"all\" flag."
			);
		}
	}

	public function testRestoreCartItemsMutation() {
		// Create/add some products to the cart.
		$cart_item_data = [
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 2,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 5,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			],
		];
		$keys           = $this->factory->cart->add( ...$cart_item_data );
		$this->factory->cart->remove( ...$keys );

		$query = '
            mutation( $input: RestoreCartItemsInput! ) {
                restoreCartItems( input: $input ) {
                    clientMutationId
                    cartItems {
                        key
                    }
                }
            }
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'keys'             => $keys,
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [ $this->expectedField( 'restoreCartItems.clientMutationId', 'someId' ) ];
		foreach ( $keys as $key ) {
			$expected[] = $this->expectedNode( 'restoreCartItems.cartItems', compact( 'key' ) );
		}

		$this->assertQuerySuccessful( $response, $expected );

		// Confirm items in cart.
		foreach ( $keys as $key ) {
			$this->assertNotEmpty(
				\WC()->cart->get_cart_item( $key ),
				"{$key} not found in cart after \"restoreCartItems\" mutation."
			);
		}
	}

	public function testEmptyCartMutation() {
		// Create/add some products to the cart.
		$product_id    = $this->factory->product->createSimple();
		$cart          = \WC()->cart;
		$cart_item_key = $cart->add_to_cart( $product_id, 1 );
		$cart_item     = $cart->get_cart_item( $cart_item_key );

		$query = '
            mutation( $input: EmptyCartInput! ) {
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

		$variables = [
			'input' => [ 'clientMutationId' => 'someId' ],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'emptyCart.clientMutationId', 'someId' ),
				$this->expectedNode(
					'emptyCart.deletedCart.contents.nodes',
					[
						'key'         => $cart_item['key'],
						'product'     => [
							'node' => [
								'id' => $this->toRelayId( 'post', $cart_item['product_id'] ),
							],
						],
						'variation'   => null,
						'quantity'    => $cart_item['quantity'],
						'subtotal'    => wc_graphql_price( $cart_item['line_subtotal'] ),
						'subtotalTax' => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
						'total'       => wc_graphql_price( $cart_item['line_total'] ),
						'tax'         => wc_graphql_price( $cart_item['line_tax'] ),
					]
				),
			]
		);

		$this->assertTrue( \WC()->cart->is_empty() );
	}

	public function testApplyCouponMutation() {
		// Create products.
		$product_id = $this->factory->product->createSimple(
			[ 'regular_price' => 100 ]
		);

		// Create coupon.
		$coupon_code = wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				[
					'amount'      => 0.5,
					'product_ids' => [ $product_id ],
					'description' => 'lorem ipsum dolor',
				]
			)
		);

		// Add items to carts.
		$cart          = \WC()->cart;
		$cart_item_key = $cart->add_to_cart( $product_id, 1 );

		$old_total = \WC()->cart->get_cart_contents_total();

		$query = '
            mutation( $input: ApplyCouponInput! ) {
                applyCoupon( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
							code
							description
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

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'code'             => $coupon_code,
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		// Get updated cart item.
		$cart_item = $cart->get_cart_item( $cart_item_key );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'applyCoupon.clientMutationId', 'someId' ),
				$this->expectedNode(
					'applyCoupon.cart.appliedCoupons',
					[
						'code'        => $coupon_code,
						'description' => 'lorem ipsum dolor',
					]
				),
				$this->expectedNode(
					'applyCoupon.cart.contents.nodes',
					[
						'key'         => $cart_item['key'],
						'product'     => [
							'node' => [
								'id' => $this->toRelayId( 'post', $cart_item['product_id'] ),
							],
						],
						'quantity'    => $cart_item['quantity'],
						'subtotal'    => wc_graphql_price( $cart_item['line_subtotal'] ),
						'subtotalTax' => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
						'total'       => wc_graphql_price( $cart_item['line_total'] ),
						'tax'         => wc_graphql_price( $cart_item['line_tax'] ),
					]
				),
			]
		);

		$new_total = \WC()->cart->get_cart_contents_total();

		// Use --debug to view.
		codecept_debug(
			[
				'old' => $old_total,
				'new' => $new_total,
			]
		);

		$this->assertTrue( $old_total > $new_total );
	}

	public function testApplyCouponMutationWithInvalidCoupons() {
		$cart = WC()->cart;

		// Create products.
		$product_id = $this->factory->product->createSimple();

		// Create invalid coupon codes.
		$coupon_id           = $this->factory->coupon->create(
			[ 'product_ids' => [ $product_id ] ]
		);
		$expired_coupon_code = wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				[
					'product_ids'  => [ $product_id ],
					'date_expires' => time() - 20,
				]
			)
		);
		$applied_coupon_code = wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				[ 'product_ids' => [ $product_id ] ]
			)
		);

		// Add items to carts.
		$cart_item_key = $cart->add_to_cart( $product_id, 1 );
		$cart->apply_coupon( $applied_coupon_code );

		$old_total = \WC()->cart->get_cart_contents_total();

		$query = '
            mutation( $input: ApplyCouponInput! ) {
                applyCoupon( input: $input ) {
                    clientMutationId
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Can't pass coupon ID as coupon 'code'. Mutation should fail.
		 */
		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'code'             => '$coupon_id',
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertNotEmpty( $response['errors'] );
		$this->assertEmpty( $response['data']['applyCoupon'] );

		/**
		 * Assertion Two
		 *
		 * Can't pass expired coupon code. Mutation should fail.
		 */
		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'code'             => $expired_coupon_code,
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertNotEmpty( $response['errors'] );
		$this->assertEmpty( $response['data']['applyCoupon'] );

		/**
		 * Assertion Three
		 *
		 * Can't pass coupon already applied to the cart. Mutation should fail.
		 */
		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'code'             => $applied_coupon_code,
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertNotEmpty( $response['errors'] );
		$this->assertEmpty( $response['data']['applyCoupon'] );

		$this->assertEquals( $old_total, \WC()->cart->get_cart_contents_total() );
	}

	public function testRemoveCouponMutation() {
		// Create product and coupon.
		$product_id  = $this->factory->product->createSimple();
		$coupon_code = wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				[ 'product_ids' => [ $product_id ] ]
			)
		);

		// Add item and coupon to cart and get total..
		$cart          = \WC()->cart;
		$cart_item_key = $cart->add_to_cart( $product_id, 3 );
		$cart->apply_coupon( $coupon_code );

		$query = '
            mutation removeCoupons( $input: RemoveCouponsInput! ) {
                removeCoupons( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
							code
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

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'codes'            => [ $coupon_code ],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		// Get updated cart item.
		$cart_item = \WC()->cart->get_cart_item( $cart_item_key );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'removeCoupons.clientMutationId', 'someId' ),
				$this->expectedField( 'removeCoupons.cart.appliedCoupons', static::IS_NULL ),
				$this->expectedNode(
					'removeCoupons.cart.contents.nodes',
					[
						'key'         => $cart_item['key'],
						'product'     => [
							'node' => [
								'id' => $this->toRelayId( 'post', $cart_item['product_id'] ),
							],
						],
						'quantity'    => $cart_item['quantity'],
						'subtotal'    => wc_graphql_price( $cart_item['line_subtotal'] ),
						'subtotalTax' => wc_graphql_price( $cart_item['line_subtotal_tax'] ),
						'total'       => wc_graphql_price( $cart_item['line_total'] ),
						'tax'         => wc_graphql_price( $cart_item['line_tax'] ),
					]
				),
			]
		);
	}
	public function testAddToCartMutationErrors() {
		// Create products.
		$product_id    = $this->factory->product->createSimple(
			[
				'manage_stock'   => true,
				'stock_quantity' => 1,
			]
		);
		$variation_ids = $this->factory->product_variation->createSome();

		$product   = \wc_get_product( $variation_ids['product'] );
		$attribute = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( 'test' );
		$attribute->set_options( [ 'yes', 'no' ] );
		$attribute->set_position( 3 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$attributes   = array_values( $product->get_attributes() );
		$attributes[] = $attribute;
		$product->set_attributes( $attributes );
		$product->save();

		$query = '
			mutation( $input: AddToCartInput! ) {
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

		\WC()->session->set( 'wc_notices', null );
		$variables          = [
			'input' => [
				'clientMutationId' => 'someId',
				'productId'        => $variation_ids['product'],
				'quantity'         => 5,
				'variationId'      => $variation_ids['variations'][0],
			],
		];
		$missing_attributes = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertArrayHasKey( 'errors', $missing_attributes );

		\WC()->session->set( 'wc_notices', null );
		$variables        = [
			'input' => [
				'clientMutationId' => 'someId',
				'productId'        => $product_id,
				'quantity'         => 5,
			],
		];
		$not_enough_stock = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertArrayHasKey( 'errors', $not_enough_stock );
	}

	public function testAddToCartMutationItemEdgeData() {
		// Create variable product for later use.
		$variation_ids = $this->factory->product_variation->createSome();
		$product       = \wc_get_product( $variation_ids['product'] );
		$attribute     = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( 'test' );
		$attribute->set_options( [ 'yes', 'no' ] );
		$attribute->set_position( 3 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$attributes   = array_values( $product->get_attributes() );
		$attributes[] = $attribute;
		$product->set_attributes( $attributes );
		$product->save();

		$query = '
			mutation( $input: AddToCartInput! ) {
				addToCart(input: $input) {
					cartItem {
						product {
							simpleVariations {
								name
								value
							}
							node {
								databaseId
							}
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'productId'        => $variation_ids['product'],
				'quantity'         => 3,
				'variationId'      => $variation_ids['variations'][1],
				'variation'        => [
					[
						'attributeName'  => 'test',
						'attributeValue' => 'yes',
					],
					[
						'attributeName'  => 'color',
						'attributeValue' => 'green',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'addToCart.cartItem.product',
				[
					$this->expectedField( 'node.databaseId', $variation_ids['product'] ),
					$this->expectedObject(
						'simpleVariations.#',
						[
							$this->expectedField( 'name', 'attribute_test' ),
							$this->expectedField( 'value', 'yes' ),
						]
					),
					$this->expectedObject(
						'simpleVariations.#',
						[
							$this->expectedField( 'name', 'attribute_pa_color' ),
							$this->expectedField( 'value', 'green' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testAddCartItemsMutationAndErrors() {
		// Create variable product for later use.
		$variation_ids = $this->factory->product_variation->createSome();
		$product       = \wc_get_product( $variation_ids['product'] );
		$attribute     = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( 'test' );
		$attribute->set_options( [ 'yes', 'no' ] );
		$attribute->set_position( 3 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$attributes   = array_values( $product->get_attributes() );
		$attributes[] = $attribute;
		$product->set_attributes( $attributes );
		$product->save();

		$product_one     = $this->factory->product->createSimple();
		$invalid_product = 1000;

		$query = '
			mutation ($input: AddCartItemsInput!) {
				addCartItems(input: $input) {
					clientMutationId
					added {
						product {
							node { databaseId }
						}
						variation {
							node { databaseId }
						}
						quantity
					}
					cartErrors {
						type
						reasons
						productId
						quantity
						variationId
						variation {
							attributeName
							attributeValue
						}
						extraData
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'items'            => [
					[
						'productId' => $product_one,
						'quantity'  => 2,
					],
					[
						'productId'   => $variation_ids['product'],
						'quantity'    => 5,
						'variationId' => $variation_ids['variations'][0],
					],
					[
						'productId' => $invalid_product,
						'quantity'  => 4,
					],
					[
						'productId'   => $variation_ids['product'],
						'quantity'    => 3,
						'variationId' => $variation_ids['variations'][1],
						'variation'   => [
							[
								'attributeName'  => 'test',
								'attributeValue' => 'yes',
							],
							[
								'attributeName'  => 'color',
								'attributeValue' => 'green',
							],
						],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'addCartItems.clientMutationId', 'someId' ),
				$this->expectedNode(
					'addCartItems.added',
					[
						'product'   => [
							'node' => [ 'databaseId' => $product_one ],
						],
						'variation' => null,
						'quantity'  => 2,
					]
				),
				$this->expectedNode(
					'addCartItems.added',
					[
						'product'   => [
							'node' => [ 'databaseId' => $variation_ids['product'] ],
						],
						'variation' => [
							'node' => [ 'databaseId' => $variation_ids['variations'][1] ],
						],
						'quantity'  => 3,
					]
				),
				$this->expectedNode(
					'addCartItems.cartErrors',
					[
						$this->expectedField( 'type', 'INVALID_CART_ITEM' ),
						$this->expectedField( 'reasons.0', static::NOT_NULL ),
						$this->expectedField( 'productId', $variation_ids['product'] ),
						$this->expectedField( 'quantity', 5 ),
						$this->expectedField( 'variationId', $variation_ids['variations'][0] ),
						$this->expectedField( 'variation', static::IS_NULL ),
						$this->expectedField( 'extraData', static::IS_NULL ),
					]
				),
				$this->expectedNode(
					'addCartItems.cartErrors',
					[
						$this->expectedField( 'type', 'INVALID_CART_ITEM' ),
						$this->expectedField( 'reasons.0', 'No product found matching the ID provided' ),
						$this->expectedField( 'productId', $invalid_product ),
						$this->expectedField( 'quantity', 4 ),
						$this->expectedField( 'variationId', static::IS_NULL ),
						$this->expectedField( 'variation', static::IS_NULL ),
						$this->expectedField( 'extraData', static::IS_NULL ),
					]
				),
			]
		);
	}

	public function testFillCartMutationAndErrors() {
		// Create products.
		$product_one = $this->factory->product->createSimple( [ 'regular_price' => 100 ] );
		$product_two = $this->factory->product->createSimple( [ 'regular_price' => 40 ] );

		// Create coupons.
		$coupon_code_one = wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				[
					'amount'      => 0.5,
					'product_ids' => [ $product_one ],
				]
			)
		);
		$coupon_code_two = wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				[
					'amount'      => 0.2,
					'product_ids' => [ $product_two ],
				]
			)
		);

		$invalid_product         = 1000;
		$invalid_coupon          = 'failed';
		$invalid_shipping_method = 'fakityfake-shipping';

		$this->factory->shipping_zone->createLegacyFlatRate();

		$query = '
			mutation ($input: FillCartInput!) {
				fillCart( input: $input ) {
					clientMutationId
					cart {
						chosenShippingMethods
						contents {
							nodes {
								product {
									node { databaseId }
								}
								quantity
								variation {
									node { databaseId }
								}
							}
						}
						appliedCoupons {
							code
							discountAmount
							discountTax
						}
					}
					cartErrors {
						type
						... on CartItemError {
							reasons
							productId
							quantity
						}
						... on CouponError {
							reasons
							code
						}
						... on ShippingMethodError {
							chosenMethod
							package
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'items'            => [
					[
						'productId' => $product_one,
						'quantity'  => 3,
					],
					[
						'productId' => $product_two,
						'quantity'  => 2,
					],
					[
						'productId' => $invalid_product,
						'quantity'  => 4,
					],
				],
				'coupons'          => [ $coupon_code_one, $coupon_code_two, $invalid_coupon ],
				'shippingMethods'  => [ 'legacy_flat_rate', $invalid_shipping_method ],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'fillCart',
				[
					$this->expectedField( 'clientMutationId', 'someId' ),
					$this->expectedObject(
						'cart',
						[
							$this->expectedField( 'chosenShippingMethods.0', 'legacy_flat_rate' ),
							$this->expectedObject(
								'contents',
								[
									$this->expectedNode(
										'nodes',
										[
											$this->expectedField( 'product.node.databaseId', $product_one ),
											$this->expectedField( 'quantity', 3 ),
											$this->expectedField( 'variation', static::IS_NULL ),
										]
									),
									$this->expectedNode(
										'nodes',
										[
											$this->expectedField( 'product.node.databaseId', $product_two ),
											$this->expectedField( 'quantity', 2 ),
											$this->expectedField( 'variation', static::IS_NULL ),
										]
									),
								]
							),
							$this->expectedNode(
								'appliedCoupons',
								[
									$this->expectedField( 'code', $coupon_code_one ),
									$this->expectedField(
										'discountAmount',
										\wc_graphql_price( \WC()->cart->get_coupon_discount_amount( $coupon_code_one, true ) )
									),
									$this->expectedField(
										'discountTax',
										\wc_graphql_price( \WC()->cart->get_coupon_discount_tax_amount( $coupon_code_one ) )
									),
								]
							),
							$this->expectedNode(
								'appliedCoupons',
								[
									$this->expectedField( 'code', $coupon_code_two ),
									$this->expectedField(
										'discountAmount',
										\wc_graphql_price( \WC()->cart->get_coupon_discount_amount( $coupon_code_two, true ) )
									),
									$this->expectedField(
										'discountTax',
										\wc_graphql_price( \WC()->cart->get_coupon_discount_tax_amount( $coupon_code_two ) )
									),
								]
							),
						]
					),
					$this->expectedNode(
						'cartErrors',
						[
							$this->expectedField( 'type', 'INVALID_CART_ITEM' ),
							$this->expectedField( 'reasons', [ 'No product found matching the ID provided' ] ),
							$this->expectedField( 'productId', $invalid_product ),
							$this->expectedField( 'quantity', 4 ),
						]
					),
					$this->expectedNode(
						'cartErrors',
						[
							$this->expectedField( 'type', 'INVALID_COUPON' ),
							$this->expectedField( 'reasons', [ "Coupon &quot;{$invalid_coupon}&quot; cannot be applied because it does not exist." ] ),
							$this->expectedField( 'code', $invalid_coupon ),
						]
					),
					$this->expectedNode(
						'cartErrors',
						[
							$this->expectedField( 'type', 'INVALID_SHIPPING_METHOD' ),
							$this->expectedField( 'chosenMethod', $invalid_shipping_method ),
							$this->expectedField( 'package', 1 ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test that fillCart reports coupon errors when coupon minimum spend is not met.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/458
	 */
	public function testFillCartReportsCouponMinimumSpendError() {
		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => 10,
				'price'         => 10,
			]
		);

		$coupon_id = $this->factory->coupon->create(
			[
				'code'           => 'minspend500',
				'discount_type'  => 'percent',
				'amount'         => 10,
				'minimum_amount' => 500,
			]
		);

		$query = '
			mutation fillCart($input: FillCartInput!) {
				fillCart(input: $input) {
					added {
						key
						product {
							node {
								databaseId
							}
						}
					}
					applied {
						code
					}
					cartErrors {
						type
						reasons
						... on CouponError {
							code
							reasons
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'items'   => [
					[
						'productId' => $product_id,
						'quantity'  => 1,
					],
				],
				'coupons' => [ 'minspend500' ],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'fillCart.added.0.product.node.databaseId', $product_id ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$applied = $this->lodashGet( $response, 'data.fillCart.applied' );
		$this->assertNull( $applied, 'Coupon should not be applied when minimum spend is not met.' );

		$cart_errors = $this->lodashGet( $response, 'data.fillCart.cartErrors', [] );
		$this->assertNotEmpty( $cart_errors, 'cartErrors should contain the coupon error.' );
		$this->assertEquals( 'INVALID_COUPON', $cart_errors[0]['type'] );
		$this->assertEquals( 'minspend500', $cart_errors[0]['code'] );
		$this->assertNotEmpty( $cart_errors[0]['reasons'], 'Coupon error should have a reason.' );
	}

	/**
	 * Test that fillCart reports errors for completely invalid coupon codes.
	 */
	public function testFillCartReportsInvalidCouponCodeError() {
		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => 10,
				'price'         => 10,
			]
		);

		$query = '
			mutation fillCart($input: FillCartInput!) {
				fillCart(input: $input) {
					added {
						key
					}
					applied {
						code
					}
					cartErrors {
						type
						reasons
						... on CouponError {
							code
							reasons
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'items'   => [
					[
						'productId' => $product_id,
						'quantity'  => 1,
					],
				],
				'coupons' => [ 'nonexistent_coupon' ],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, [] );

		$applied = $this->lodashGet( $response, 'data.fillCart.applied' );
		$this->assertNull( $applied, 'Invalid coupon should not be applied.' );

		$cart_errors = $this->lodashGet( $response, 'data.fillCart.cartErrors', [] );
		$this->assertNotEmpty( $cart_errors, 'cartErrors should contain the invalid coupon error.' );
		$this->assertEquals( 'INVALID_COUPON', $cart_errors[0]['type'] );
	}

	/**
	 * Test that applyCoupon returns the same error message regardless of coupon code casing.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/600
	 */
	public function testApplyCouponErrorMessageConsistentAcrossCasing() {
		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => 10,
				'price'         => 10,
			]
		);

		\WC()->cart->add_to_cart( $product_id );

		$query = '
			mutation applyCoupon($input: ApplyCouponInput!) {
				applyCoupon(input: $input) {
					cart {
						appliedCoupons {
							code
						}
					}
				}
			}
		';

		$expected_error = [
			$this->expectedErrorMessage( 'does not exist', self::MESSAGE_CONTAINS ),
		];

		// Test with lowercase.
		$variables = [ 'input' => [ 'code' => 'fakecoupon' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQueryError( $response, $expected_error );

		// Test with uppercase.
		$variables = [ 'input' => [ 'code' => 'FAKECOUPON' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQueryError( $response, $expected_error );

		// Test with mixed case.
		$variables = [ 'input' => [ 'code' => 'FakeCoupon' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQueryError( $response, $expected_error );
	}

	/**
	 * Test that removeCoupons recalculates cart totals after coupon removal.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/260
	 */
	public function testRemoveCouponsRecalculatesTotals() {
		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => 100,
				'price'         => 100,
			]
		);

		$coupon_id = $this->factory->coupon->create(
			[
				'code'          => 'half-off',
				'discount_type' => 'percent',
				'amount'        => 50,
			]
		);

		// Add product to cart.
		\WC()->cart->add_to_cart( $product_id );

		// Apply coupon.
		$apply_query = '
			mutation applyCoupon($input: ApplyCouponInput!) {
				applyCoupon(input: $input) {
					cart {
						total
						discountTotal
						appliedCoupons {
							code
						}
					}
				}
			}
		';

		$query     = $apply_query;
		$variables = [ 'input' => [ 'code' => 'half-off' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, [] );

		$total_with_coupon    = $this->lodashGet( $response, 'data.applyCoupon.cart.total' );
		$discount_with_coupon = $this->lodashGet( $response, 'data.applyCoupon.cart.discountTotal' );

		$this->assertNotEmpty( $discount_with_coupon, 'Discount should be applied.' );

		// Remove coupon.
		$remove_query = '
			mutation removeCoupons($input: RemoveCouponsInput!) {
				removeCoupons(input: $input) {
					cart {
						total
						discountTotal
						discountTax
						appliedCoupons {
							code
						}
					}
				}
			}
		';

		$variables = [ 'input' => [ 'codes' => [ 'half-off' ] ] ];
		$response  = $this->graphql(
			[
				'query'     => $remove_query,
				'variables' => $variables,
			]
		);
		$this->assertQuerySuccessful( $response, [] );

		// Coupon should be removed.
		$applied = $this->lodashGet( $response, 'data.removeCoupons.cart.appliedCoupons' );
		$this->assertEmpty( $applied, 'No coupons should be applied after removal.' );

		// Totals should be recalculated — discount should be zero.
		$discount_after = $this->lodashGet( $response, 'data.removeCoupons.cart.discountTotal' );
		$total_after    = $this->lodashGet( $response, 'data.removeCoupons.cart.total' );

		$this->assertNotEquals(
			$total_with_coupon,
			$total_after,
			'Cart total should change after removing coupon.'
		);
	}
}
